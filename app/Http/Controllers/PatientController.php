<?php

namespace App\Http\Controllers;
use App\Patients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{

    protected function validator(array $data, Patients $patient = null)
    {
        if ($patient == null){
            return Validator::make($data, [
                'Names' => ['required', 'string', 'max:100'],
                'MedicalNumber' => ['required', 'unique:patients,MedicalNumber','string', 'max:50'],
                'BOD' => ['required', 'date'],
                'NumberPhone1' => ['required', 'string', 'max:20'],
                'NumberPhone2' => ['required', 'string', 'max:20'],
                'ContactPreference' => ['required', 'string', 'max:20'],
                'PatientAddress' => ['required', 'string', 'max:100'],
                'Email' => ['required', 'string', 'max:50'],
                'patient_types' => ['required', 'integer'],
            ]);
        }else{
            return Validator::make($data, [
                'Names' => ['required', 'string', 'max:100'],
                'MedicalNumber' => ['required','string', 'max:50', Rule::unique('patients')->ignore($patient->Id)],
                'BOD' => ['required', 'date'],
                'NumberPhone1' => ['required', 'string', 'max:20'],
                'NumberPhone2' => ['required', 'string', 'max:20'],
                'ContactPreference' => ['required', 'string', 'max:20'],
                'PatientAddress' => ['required', 'string', 'max:100'],
                'Email' => ['required', 'string', 'max:50'],
                'patient_types' => ['required', 'integer'],
            ]);
        }
     
    }

  

    public function index()
    {
        $patients = Patients::paginate(15);
        return view('admin.patients.index',['patients'=>$patients]);
    }

    public function create()
    {
        $drivers = DB::table('driver_assigments')->where('Enable',1)->pluck('Driver','Id');
        $centers = DB::table('medical_centers')->pluck('Name','IdMedicalC');
        $patient_types = DB::table('patient_types')->pluck('Name','Id');
        return view('admin.patients.create',['drivers' => $drivers, 'centers' => $centers,'patient_types' => $patient_types ]); 

    }

    public function store(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return redirect('/resource-patients-create')
                ->withErrors($validator)
                ->withInput();
        }else{

            $patient = new Patients;
            $patient->Names = $request->Names;
            $patient->MedicalNumber = $request->MedicalNumber;
            $patient->BOD = $request->BOD;
            $patient->NumberPhone1 = $request->NumberPhone1;
            $patient->NumberPhone2 = $request->NumberPhone2;
            $patient->ContactPreference = $request->ContactPreference;
            $patient->PatientAddress = $request->PatientAddress;
            $patient->Email = $request->Email;
            $patient->patient_types = $request->patient_types;
            $patient->IdMedicalC = $request->IdMedicalC;
            $patient->driver = $request->driver;
            $patient->PhysicalLimits = $request->specialrequeriment;
            $patient->PreferredLanguage = "English";
            $patient->PhysicalLimits = json_encode($request->PhysicalLimits);

            if (isset($request->PhysicalLimits)){
                $req = "";
                foreach($request->PhysicalLimits as $key => $val){
                    if (count($request->PhysicalLimits) - 1 == $key){
                        $req = $req . $val;
                    }else{
                        $req = $req . $val . "," ;
                    }
                }
                $patient->format_requeriment = $req;

            }
            $patient->save();
    
            return redirect()->route('resource.patients');

        }
        

    }

    public function edit(Request $request){

        $drivers = DB::table('driver_assigments')->where('Enable',1)->pluck('Driver','Id');
        $centers = DB::table('medical_centers')->pluck('Name','AddressMedicalC');
        $patient_types = DB::table('patient_types')->pluck('Name','Id');
        $patient = Patients::where('Id', $request->id)->first();

        return view('admin.patients.edit',
            [
                'drivers' => $drivers, 
                'centers' => $centers,
                'patient_types' => $patient_types,
                'patient' => $patient
            ]); 

    }

    public function update(Request $request){

        $patient = Patients::find($request->id);
        $validator = $this->validator($request->all(),$patient);
      
        if ($validator->fails()) {
            return redirect('/resource-patients')
                ->withErrors($validator)
                ->withInput(['patient' =>$patient]);
        }else{

            
            if (isset($request->PhysicalLimits)){
                $req = "";
                foreach($request->PhysicalLimits as $key => $val){
                    if (count($request->PhysicalLimits) - 1 == $key){
                        $req = $req . $val;
                    }else{
                        $req = $req . $val . "," ;
                    }
                }
            }else{
                $req = $patient->format_requeriment;
            }
          
            DB::update('update patients set 
            Names = ?, 
            MedicalNumber = ?, 
            BOD = ?, 
            NumberPhone1 = ?, 
            NumberPhone2 = ?, 
            ContactPreference = ?, 
            PatientAddress = ?,
            Email = ?,
            patient_types = ?,
            IdMedicalC = ?,
            driver = ?,
            PhysicalLimits = ?,
            format_requeriment = ?
            where Id = ?', 
            [ $request->Names,
              $request->MedicalNumber,
              $request->BOD,
              $request->NumberPhone1,
              $request->NumberPhone2,
              $request->ContactPreference,
              $request->PatientAddress,
              $request->Email,
              $request->patient_types,
              $request->IdMedicalC,
              $request->driver,
              json_encode($request->PhysicalLimits),
              $req,
              $patient->Id]);

            return redirect()->route('resource.patients');

           }
    }


    public function getPatient(Request $request){
 
        $patient =  $request->get('patientname');
        $patients = Patients::orderBy( 'Id', 'DESC')
                    ->patient($patient)
                    ->paginate(10);
        return view('patients.searchpatient',['patients' => $patients]);

    }
}
