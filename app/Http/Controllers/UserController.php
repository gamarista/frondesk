<?php

namespace App\Http\Controllers;
use App\User;
use App\Driver_assigments;
use App\Validators\userValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    public function index(){

        $users = User::paginate(15);
        return view('admin.users.index',['users'=>$users]);

    }

    public function create(){

        $centers = DB::table('medical_centers')->pluck('Name','IdMedicalC');
        $zones = DB::table('zones')->pluck('Name','IdZone');
        $vehicles = DB::table('vehicles')->where('Enable',1)->pluck('Model','IdVehicle');
        $roles = DB::table('user_rols')->pluck('Name','IdRole');

        return view('admin.users.create',
            [
                'centers'=>$centers,
                'zones'=>$zones,
                'vehicles'=>$vehicles,
                'roles'=>$roles
            ]);

    }

    public function store(Request $request){

        $userValidator = new userValidator();
        $validator = $userValidator->validator($request->all(),null,$request->driver_card_number,null);

        if ($validator->fails()) {
            return redirect('/resource-users-create')
                ->withErrors($validator)
                ->withInput();
        }else{
            switch ($request->IdRole) {
                case '1':
                    $tmpTipo='AD';
                    break;
                case '2':
                    $tmpTipo='DI';
                    break;
                case '3':
                    $tmpTipo='DA';
                    break;
                case '4':
                    $tmpTipo='FD';
                    break;                                                        
                case '5':
                    $tmpTipo='DR';
                    break;                    
            }



            $user = New User; 
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->IdRole = $request->IdRole;
            $user->userType = $tmpTipo;
            $user->save();
        
            if (strcmp($user->role->Name,'driver') == 0 ){
                $driver = new Driver_assigments;
                $driver->Driver = $user->name;
                $driver->driver_card_number = $request->driver_card_number;
                $driver->Phone1 = $request->Phone1;
                $driver->Address = $request->Address;
                $driver->IdVehicle = $request->IdVehicle;
                $driver->dZone = $request->dZone;
                $driver->IdMC = $request->IdMC;
                $driver->user_id = $user->id;
                $driver->save();

            }

         
    
            return redirect()->route('resource.users');

        }

    }

    public function edit(Request $request){

        $user = User::find($request->id);
        $centers = DB::table('medical_centers')->pluck('Name','IdMedicalC');
        $zones = DB::table('zones')->pluck('Name','IdZone');
        $vehicles = DB::table('vehicles')->where('Enable',1)->pluck('Model','IdVehicle');
        $roles = DB::table('user_rols')->pluck('Name','IdRole');
        $driver = Driver_assigments::where('user_id',$user->id)->first();
        
        return view('admin.users.edit',[
            'centers'=>$centers,
            'zones'=>$zones,
            'vehicles'=>$vehicles,
            'roles'=>$roles,
            'user' => $user,
            'driver' => $driver
        ]); 
    }

    public function update(Request $request){
      
        $user = User::find($request->id);
        $driver = Driver_assigments::where('Id', $request->Id)->first();
        $userValidator = new userValidator();
        $validator = $userValidator->validator($request->all(),$user,$request->driver_card_number, $driver);
        
        if ($validator->fails()) {
            
            return redirect('/resource-users')
                ->withErrors($validator)
                ->withInput();
        }else{
            switch ($request->IdRole) {
                case '1':
                    $tmpTipo='AD';
                    break;
                case '2':
                    $tmpTipo='DI';
                    break;
                case '3':
                    $tmpTipo='DA';
                    break;
                case '4':
                    $tmpTipo='FD';
                    break;                                                        
                case '5':
                    $tmpTipo='DR';
                    break;                    
            }       
            DB::update('update users set 
            name = ?, 
            email = ?, 
            IdRole = ?,
            userType = ?
            where id = ?', 
            [ $request->name,
              $request->email,
              $request->IdRole,
              $tmpTipo,
              $user->id]);

            $freshUser = $user->fresh();

            if (strcmp($freshUser->role->Name,'driver') != 0 && isset($driver)){

                DB::update('update driver_assigments set 
                Enable = ?
                where Id = ?', 
                [0,$driver->Id]);

            }

            if (strcmp($freshUser->role->Name,'driver') == 0 && isset($driver)){
                DB::update('update driver_assigments set 
                driver_card_number = ?, 
                Phone1 = ?, 
                Address = ?, 
                IdVehicle = ?, 
                dZone = ?, 
                IdMC = ?,
                Enable = ?
                where Id = ?', 
                [
                $request->driver_card_number,
                $request->Phone1,
                $request->Address,
                $request->IdVehicle,
                $request->dZone,
                $request->IdMC,
                1,
                $driver->Id]);

            }elseif(strcmp($freshUser->role->Name,'driver') == 0 && !isset($driver)){

                $driver = new Driver_assigments;
                $driver->Driver = $freshUser->name;
                $driver->driver_card_number = $request->driver_card_number;
                $driver->Phone1 = $request->Phone1;
                $driver->Address = $request->Address;
                $driver->IdVehicle = $request->IdVehicle;
                $driver->dZone = $request->dZone;
                $driver->IdMC = $request->IdMC;
                $driver->user_id = $freshUser->id;
                $driver->save();

            }

         
    
            return redirect()->route('resource.users');

        }

    }

    public function resetPassword(Request $request){
        return view('admin.users.resetPassword',['user' => $request->id]);
    }

    public function savePassword(Request $request){

        $user = User::find($request->id);
        $checkOldPassword = Hash::check($request->oldpassword, $user->password);
        $userValidator = new userValidator();
        $validator = $userValidator->validatorResetPassword(array_merge($request->all(), ['checkOldPassword' => $checkOldPassword]));

        if ($validator->fails()) {
            
            return redirect('/resource-users-reset-password')
                ->withErrors($validator)
                ->withInput();
        }else{
     
            DB::update('update users set 
            password = ?
            where id = ?', 
            [ Hash::make($request->password),
              $user->id]);

            return redirect()->route('resource.users');
           
        }
        
    }

    public function status(Request $request){

        if ($request->ajax()){

            $user = User::find($request->id);
            if ( $user->enable == 1){
                DB::update('update users set enable = 0 where id = ?', [$user->id]);
                if (isset($user->driver))
                    DB::update('update driver_assigments set Enable = 0 where Id = ?', [$user->driver->Id]);
                $user->enable = 0;
              

            }else{
                DB::update('update users set enable = 1 where id = ?', [$user->id]);
                if (isset($user->driver))
                    DB::update('update driver_assigments set Enable = 1 where Id = ?', [$user->driver->Id]);
                $user->enable = 1;
            }

            $response = [
                'status' => $user->enable,
                'email' => $user->email
            ];
       
            return response(  $response , 200);

        }
    }
    //
}
