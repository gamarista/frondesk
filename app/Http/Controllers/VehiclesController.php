<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Vehicles;

class VehiclesController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */

    public function messagesValidator()
    {
        $messages = [
            'VehicleReg.unique' => 'The license has been taked',
          
        ];
        return $messages;
    }

    protected function validator(array $data, Vehicles $vehicle = null)
    {
        if ($vehicle == null){
           
            return Validator::make($data, [
                'Model' => ['required', 'string', 'max:20'],
                'VehicleBrand' => ['required', 'string', 'max:20'],
                'VehicleReg' => ['required', 'unique:vehicles,VehicleReg','string', 'max:30'],
                'NumSeats' => ['required', 'integer', 'max:40'],
                'Notes' => ['required', 'string', 'max:255']
               
            ],$this->messagesValidator());

        }else{

            return Validator::make($data, [
                'Model' => ['required', 'string', 'max:20'],
                'VehicleBrand' => ['required', 'string', 'max:20'],
                'VehicleReg' => ['required', 'string', 'max:30', Rule::unique('vehicles')->ignore($vehicle->IdVehicle, 'IdVehicle')],
                'NumSeats' => ['required', 'integer', 'max:40'],
                'Notes' => ['required', 'string', 'max:255']
            ],$this->messagesValidator());

        }
       
    }

    public function index(){

        $vehicles = Vehicles::paginate(15);
        return view('admin.vehicles.index',['vehicles'=>$vehicles]);
    }

    public function create(){

        return view('admin.vehicles.create');
    }

    public function store(Request $request){

        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return redirect('/resource-vehicles-create')
                ->withErrors($validator)
                ->withInput();
        }else{


            $vehicle = new Vehicles;
            $vehicle->Model = $request->Model;
            $vehicle->VehicleBrand = $request->VehicleBrand;
            $vehicle->VehicleReg = $request->VehicleReg;
            $vehicle->NumSeats = $request->NumSeats;
            $vehicle->Notes = $request->Notes;
            $vehicle->Enable = 1;
            $vehicle->save();
    
            return redirect()->route('resource.vehicles');

        }
        
    }

    public function edit(Request $request){

        $vehicle = Vehicles::where('IdVehicle', $request->id)->first();
        return view('admin.vehicles.edit',['vehicle'=>$vehicle]); 

    }

    public function update(Request $request){

        $vehicle = Vehicles::where('IdVehicle', $request->id)->first();
        $validator = $this->validator($request->all(),$vehicle);
        if ($validator->fails()) {
            return redirect('/resource-vehicles')
                ->withErrors($validator);
          
        }else{
            
            DB::update('update vehicles set 
            Model = ?, 
            VehicleBrand = ?, 
            VehicleReg = ?, 
            NumSeats = ?, 
            Notes = ?
            where IdVehicle = ?', 
            [ $request->Model,
              $request->VehicleBrand,
              $request->VehicleReg,
              $request->NumSeats,
              $request->Notes,
              $vehicle->IdVehicle]);
    
            return redirect()->route('resource.vehicles');

        }

    }

    public function status(Request $request){

        if ($request->ajax()){

            $vehicle = Vehicles::where('IdVehicle', $request->id)->first();
           
            if ( $vehicle->Enable == 1){
                DB::update('update vehicles set Enable = 0 where IdVehicle = ?', [$vehicle->IdVehicle]);
                $vehicle->Enable = 0;
              

            }else{
                DB::update('update vehicles set Enable = 1 where IdVehicle = ?', [$vehicle->IdVehicle]);
                $vehicle->Enable = 1;
            }

            $response = [
                'status' => $vehicle->Enable,
                'vehicle' => $vehicle->VehicleReg
            ];
       
            return response($response , 200);

        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
/*
    protected function new(Request $data)
    {
     $data['Enable'] = $data->has('Enable');
     $this->validator($data->all())->validate();
     
     $vehicle=Vehicles::where(['IdVehicle'=>$data->IdVehicle])->get();
	 if ($vehicle->count()>0)
	 { 
	   $Vehicle = Vehicles::Where(['IdVehicle' => $data->IdVehicle])
	   ->update([
	        	'Model'=>$data->Model,
	        	'VehicleBrand'=>$data->VehicleBrand,
	        	'VehicleReg'=>$data->VehicleReg,
	        	'NumSeats'=>$data->NumSeats,
	        	'Enable'=>$data->Enable,
	        ]);
	   $msg="Vehicle updated";
       $msge="";
	   $data=Vehicles::where(['IdVehicle'=>$data->IdVehicle])->get();
	   $data=json_encode($data);
	   $vehicles=Vehicles::all();
	   return view('vehicles',['vehicles'=>$vehicles,'data'=>$data,'status'=>$msg]); 
     }
     else
     {	
   	    $vehicle=Vehicles::create($data->all());
   	    $vehicles=Vehicles::all();	    
        if ($vehicle)
            {
             $msg=$data->Model.'-'.$data->VehicleBrand." vehicle successfully registered";
             return view('vehicles',['vehicles'=>$vehicles,'status'=>$msg]);
            }
        else
            {
             $msge="An error occurred while registering the vehicle";
             return view('vehicles',['vehicles'=>$vehicles,'statuse'=>$msge]);
            }
      }
    }   

    protected function update(Request $data)
    {
        $Vehicle = Vehicles::Where(['IdVehicle' => $data->IdVehicle,'VehicleReg'=>$data->VehicleReg])
        ->update([
        	'Model'=>$data->Model,
        	'VehicleBrand'=>$data->VehicleBrand,
        	'VehicleReg'=>$data->VehicleReg,
        	'NumSeats'=>$data->NumSeats,
        	'Enable'=>$data->Enable,
        ]);
        if ($Vehicle)
            {
             $msg="Vehicle updated";
             $msge="";
	         $data=Vehicles::where(['IdVehicle'=>$request->IdVehicle])->get();
	         $data=json_encode($data);	         

            }
        else
            {
             $msg="";
             $msge="Error";
             $data='';
            }
        //return redirect()->route('Vehicles')->with(['status'=>$msg,'statuse'=>$msge]);
        $vehicles=Vehicles::all();    
        return view('vehicles',['vehicles'=>$vehicles,'data'=>$data,'status'=>$msg,'statuse'=>$msge]);
    }  

    public function index()
    {
        $vehicles=Vehicles::all();
        return view('vehicles',['vehicles'=>$vehicles]);
    }  

    public function search(Request $request)
    {
        $data=Vehicles::where(['IdVehicle'=>$request->IdVehicle])->get();
        $vehicles=Vehicles::all();
        $data=json_encode($data);
        return view('vehicles',['vehicles'=>$vehicles,'data'=>$data]);
    }      */
}
