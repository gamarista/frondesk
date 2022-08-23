<?php
namespace App\Components;

use App\traits\TripsPlannerTrait;
use App\Interfaces\ISheetData;
use App\Appoinments;
use App\Patients;
use App\ParamsTimeFilter;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

//business Logic

class  AppoinmentLogComponent implements ISheetData
{
        use TripsPlannerTrait;

        public function getData($request){
                
                $sort =  $request->get('sort_by');
                $params = [
                    'driver' =>  $request->get('driver'),
                    'center' =>  $request->get('center'),
                    'patient_name' => $request->get('patient_name'),
                    'appoinment' =>  $request->get('appoinment'),
                    'tripType' => $request->get('tripType'),
                    'status' => $request->get('status'),
                    'destino' => $request->get('destino'),
                    'hora' => $request->get('hora'),
                    'horae' => $request->get('horae'),
                    'dateFilter' => $request->get('date')
                ];
            
                switch ($sort) {
                    case 1:
                        $sort = 'FirstName';
                        break;
                    case 2:
                        $sort = 'Time';
                        break;
        
                    default:
                        $sort = 'id';
                } 
        
                $destinos = Appoinments::groupBy('AddressDestination')
                ->selectRaw('AddressDestination')
                ->where('TripType', '=', 'A')
                ->get()->pluck('AddressDestination','AddressDestination');
                $drivers = DB::table('driver_assigments')->where('Enable', 1)->pluck('Driver', 'Id');
                $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');
                //$date = Carbon::today()->format('Y-m-d');
                //$dt = Carbon::create('2020-09-08');
        
                $appoinments = Appoinments::getTrips($params,$sort,$centers); 
                
                $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
                foreach ($appoinments as $key => $value) {
                    if (isset($value->IdMC) == false) {
                        $appoinments[$key]['IdMC'] = $patientDummy->Id;
                    }
                }
                $ParamsTimeFilter = DB::table('params_time_filters')->pluck('value','name');
                return [
                    'destinationPlace' =>  $destinos,
                    'driver' => $drivers,
                    'centers' => $centers,
                    'cancellation' => null,
                    'date' =>$request->get('date'),
                    'appoinments' => $appoinments,
                    'horas' => $ParamsTimeFilter,
        
                ];
        }
}