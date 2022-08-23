<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Medical_centers;

class MedicalCenterController extends Controller
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
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'Name' => ['required', 'string', 'max:200'],
            'NickName' => ['required', 'string', 'max:100'],
            'AddressMedicalC' => ['required', 'string', 'max:250'],
            'NumberPhone' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'max:50'],
            'fax' => ['required', 'string', 'max:50'],
            'phone2' => ['required', 'string', 'max:50'],
            //'specialty' => ['required', 'string', 'max:50'],
            //'doctor' => ['required', 'string', 'max:50'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */

    protected function store(Request $request)
    {
       
        $validator = $this->validator($request->all());
      
        if ($validator->fails()) {
            return redirect('/resource-centers-create')
                ->withErrors($validator)
                ->withInput();
        }else{

            $center = new Medical_centers;
            $center->Name = $request->Name;
            $center->NickName = $request->NickName;
            $center->NumberPhone = $request->NumberPhone;
            $center->NumberPhone1 = $request->NumberPhone;
            $center->AddressMedicalC = $request->AddressMedicalC;
            $center->Specialty = $request->specialty;
            $center->NameDr = $request->doctor;
            $center->NumberPhone2 = $request->phone2;
            $center->FaxNumber = $request->fax;
            $center->Email = $request->email;
            $center->save();
    
            // Here comes the calculate to the coordenates
            // tambien se deberia hacer una validacion por si al obtener las coordenadas se fracasa

            return redirect()->route('resource.centers');

        }
  
    }   

    protected function update(Request $request)
    {
       
        $validator = $this->validator($request->all());
      
        if ($validator->fails()) {
            return redirect('/resource-centers')
                ->withErrors($validator)
                ->withInput();
        }else{

          
            // Here comes the calculate to the coordenates
            // tambien se deberia hacer una validacion por si al obtener las coordenadas se fracasa

            Medical_centers::where('IdMedicalC', $request->id)->update(
                array(
                    'Name' => $request->Name,
                    'NickName' => $request->NickName,
                    'NumberPhone' =>  $request->NumberPhone,
                    'NumberPhone1' =>  $request->NumberPhone,
                    'AddressMedicalC' => $request->AddressMedicalC,
                    'Specialty' => $request->specialty,
                    'NameDr' => $request->doctor,
                    'NumberPhone2' => $request->phone2,
                    'FaxNumber' => $request->fax,
                    'Email' => $request->email
    
                ));
    
            return redirect()->route('resource.centers');

        }
  
    }   

    public function index()
    {
        $centers = Medical_centers::paginate(15);
        return view('admin.centers.index',['centers'=> $centers]);
    }  

    public function create()
    {
       
        return view('admin.centers.create');
    }  

    public function edit(Request $request){
        
        $center = Medical_centers::where('IdMedicalC', $request->id)->first();
        
        return view('admin.centers.edit',['center'=>$center]);
    }
    
    public function getInfoCenter(Request $request){
       
        if ($request->ajax()){
            $center = Medical_centers::where('Name',$request->center)->first();
            return response($center->toJson(), 200);
        }
    }
}
