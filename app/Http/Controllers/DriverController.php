<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Driver_assigments;
use App\ges_appoinments;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'Driver' => ['required', 'string', 'max:100'],
            'driver_card_number' => ['required', 'unique:driver_assigments,driver_card_number','string', 'max:50'],
            'Phone1' => ['required', 'string', 'max:20'],
            'Address' => ['required', 'string', 'max:100'],
            'IdVehicle' => ['required', 'integer'],
            'dZone' => ['required', 'integer'],
            'IdMC' => ['required', 'integer'],
            'email' => ['required', 'unique:users,email','string', 'max:50'],
            'password' => ['required','in:'.$data['confirm_password'], 'string', 'max:20'],
            'confirm_password' => ['required', 'string', 'max:20'],
         
        ]);
    }

    protected function validator_update(array $data, Driver_assigments $driver )
    {
        return Validator::make($data, [
            'Driver' => ['required', 'string', 'max:100'],
            'driver_card_number' => ['required','string', 'max:50', Rule::unique('driver_assigments')->ignore($driver->Id)],
            'Phone1' => ['required', 'string', 'max:20'],
            'Address' => ['required', 'string', 'max:100'],
            'IdVehicle' => ['required', 'integer'],
            'dZone' => ['required', 'integer'],
            'IdMC' => ['required', 'integer'],
         
        ]);
    }

    public function index()
    {
        $drivers = Driver_assigments::paginate(15);
        return view('admin.drivers.index',['drivers'=> $drivers ]);
      
    }  


    public function create()
    {
        $centers = DB::table('medical_centers')->pluck('Name','IdMedicalC');
        $zones = DB::table('zones')->pluck('Name','IdZone');
        $vehicles = DB::table('vehicles')->where('Enable',1)->pluck('Model','IdVehicle');

        return view('admin.drivers.create',
                    ['centers'=> $centers,
                    'zones'=> $zones,
                    'vehicles'=> $vehicles, 
                    ]);
      
    }
    
    public function store(Request $request){

        $validator = $this->validator($request->all());
      
        if ($validator->fails()) {
            return redirect('/resource-drivers-create')
                ->withErrors($validator)
                ->withInput();
        }else{

            $role = DB::table('user_rols')->where('Name', 'driver')->first();

            $user = new User();
            $user->name = $request->Driver;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);;
            $user->IdRole = $role->IdRole;
            $user->save();
            $user->refresh();

            $driver = new Driver_assigments;
            $driver->Driver = $request->Driver;
            $driver->driver_card_number = $request->driver_card_number;
            $driver->Phone1 = $request->Phone1;
            $driver->Address = $request->Address;
            $driver->IdVehicle = $request->IdVehicle;
            $driver->dZone = $request->dZone;
            $driver->IdMC = $request->IdMC;
            $driver->user_id = $user->id;
            $driver->save();

            return redirect()->route('resource.drivers');

        }

    }

    public function edit(Request $request){

        $driver = Driver_assigments::where('Id', $request->id)->first();
        $centers = DB::table('medical_centers')->pluck('Name','IdMedicalC');
        $zones = DB::table('zones')->pluck('Name','IdZone');
        $vehicles = DB::table('vehicles')->where('Enable',1)->pluck('Model','IdVehicle');
        
        return view('admin.drivers.edit',
            [
                'driver'=>$driver,
                'centers'=> $centers,
                'zones'=> $zones,
                'vehicles'=> $vehicles, 
            ]);

    }

    public function update(Request $request){
        $driver = Driver_assigments::find($request->id);
        $validator = $this->validator_update($request->all(),$driver);
      
        if ($validator->fails()) {
            return redirect('/resource-drivers')
                ->withErrors($validator)
                ->withInput();
        }else{

            DB::update('update driver_assigments set 
            Driver = ?, 
            driver_card_number = ?, 
            Phone1 = ?, 
            Address = ?, 
            IdVehicle = ?, 
            dZone = ?, 
            IdMC = ?
            where Id = ?', 
            [ $request->Driver,
              $request->driver_card_number,
              $request->Phone1,
              $request->Address,
              $request->IdVehicle,
              $request->dZone,
              $request->IdMC,
              $driver->Id]);
      
            return redirect()->route('resource.drivers');

        }

    }

    public function status(Request $request){

        if ($request->ajax()){

            $driver = Driver_assigments::find($request->id);
            if ( $driver->Enable == 1){
                DB::update('update driver_assigments set Enable = 0 where Id = ?', [$driver->Id]);
                if (isset($driver->user)){
                    DB::update('update users set enable = 0 where id = ?', [$driver->user->id]);
                }
             
                $driver->Enable = 0;
              

            }else{
                DB::update('update driver_assigments set Enable = 1 where Id = ?', [$driver->Id]);
                if (isset($driver->user))
                    DB::update('update users set enable = 1 where id = ?', [$driver->user->id]);
                $driver->Enable = 1;
            }

            $response = [
                'status' => $driver->Enable,
                'driver' => $driver->Driver
            ];
       
            return response(  $response , 200);

        }
    }

    public function routeStatus(Request $request){

       $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');
       $zones = DB::table('zones')->pluck('Name', 'IdZone');

       $zone =  $request->get('zone');
       $center =  $request->get('center');



       if (!empty($zone) || !empty($center)){
            
            //dd($center);
        
        $tripStatus = ges_appoinments::select(DB::raw('SUM(notified_driver)  as total_trips, count(*) as trips_not_sent,SUM(dist) as total_distance,medical_centers.Name as center,driver_id'))
            //$tripStatus = ges_appoinments::select(DB::raw('SUM(notified_driver)  as total_trips, count(*) as trips_not_sent,SUM(distance) as total_distance,driver_id'))
        //->Join('medical_centers', 'ges_appoinments.AddressDestination', '=', 'medical_centers.AddressMedicalC')
        
        ->Join('driver_assigments','ges_appoinments.driver_id', '=', 'driver_assigments.Id')
        ->Join('zones', 'driver_assigments.dZone', '=', 'zones.IdZone')
        ->where([
            ['TripType', '=', 'A'],
            ['driver_id', '<>', null],
        ])
        ->center($center)
        ->zone($zone)
        ->groupBy('medical_centers.Name', 'driver_id')
        //->groupBy('driver_id')
        ->orderBy('medical_centers.Name', 'ASC')
        //->orderBy('driver_id', 'ASC')
        ->paginate(20);
       
        }else{

            //$tripStatus = ges_appoinments::select(DB::raw('SUM(notified_driver)  as total_trips, count(*) as trips_not_sent,SUM(distance) as total_distance,medical_centers.Name as center,driver_id'))
            $tripStatus = ges_appoinments::select(DB::raw('SUM(notified_driver)  as total_trips, count(*) as trips_not_sent,SUM(dist) as total_distance,driver_id'))
            ->Join('medical_centers', 'ges_appoinments.AddressDestination', '=', 'medical_centers.AddressMedicalC')
             ->where([
                ['TripType', '=', 'A'],
                ['driver_id', '<>', null],
            ])
            ->whereIn('AddressDestination', $centers->keys())
            //->groupBy('medical_centers.Name', 'driver_id')
            ->groupBy('driver_id')
            //->orderBy('medical_centers.Name', 'ASC')
            ->orderBy('driver_id', 'ASC')
            ->paginate(20);
           
        }

        /*dd($tripStatus);
            foreach($tripStatus as $status ){
                dd($status->driver->zones->Name);
            }*/

        return view(
            'routes.routesStatus',
            [
                'centers' => $centers,
                'zones' => $zones,
                'trips_status' => $tripStatus

            ]
        );
    }

    public function sentTrips(Request $request){

        if ($request->ajax()) {

            DB::table('ges_appoinments')->whereIn('driver_id', $request->drivers)->update(array('notified_driver' => 1));
          
            $response = [
                'data' => true
            ];

            return response($response, 200);
        }

    }

  

    

}
    //

