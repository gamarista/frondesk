<?php

namespace App\Components;

use App\traits\TripsPlannerTrait;
use App\Interfaces\ISheetData;
use App\ges_appoinments;
use App\Patients;
use App\ParamsTimeFilter;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

//business Logic

class  FrontdeskTripsPlannerComponent
{
    use TripsPlannerTrait;


    public function getDataTypeB($request)
    {

        $sort =  $request->get('sort_by');

        $params = [
            'driver' =>  $request->get('driver'),
            'center' =>  $request->get('center'),
            'patient_name' => $request->get('patient_name'),
            'appoinment' =>  $request->get('appoinment'),
            //'tripType' => $request->get('tripType'),
            'tripType' => 'B',
            'confirmTrip' => $request->get('confirmTrip') == null ? False : $request->get('confirmTrip'),
            'status' => $request->get('status'),
            'destino' => $request->get('destino'),
            'hora' => $request->get('hora'),
            'horae' => $request->get('horae'),
            'init_date' => $request->get('init_date'),
            'date_end' => $request->get('date_end'),
            'date' => $request->get('date')
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

        $destinos = ges_appoinments::groupBy('AddressDestination')
            ->selectRaw('AddressDestination')
            ->where('TripType', '=', 'B')
            ->get()->pluck('AddressDestination', 'AddressDestination');

        $drivers = DB::table('driver_assigments')->where('Enable', 1)->pluck('Driver', 'Id');
        $centers = DB::table('medical_centers')->pluck('Name', 'idMedicalC');
        $cancellation = DB::table('cancellation_code')->pluck('CANCELLATION_TEXT', 'CANCELLATION_CODE');
        $date = Carbon::today()->format('Y-m-d');
        $dt = Carbon::create('2020-09-08');

        $appoinments = ges_appoinments::getTripsTypeBNotConfirmed($params, $sort, $centers);
        $totalFilter = $appoinments->count();
        $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
        foreach ($appoinments as $key => $value) {
            if (isset($value->IdMC) == false) {
                $appoinments[$key]['IdMC'] = $patientDummy->Id;
            }
        }

        $ParamsTimeFilter = DB::table('params_time_filters')->pluck('value', 'name');

        return [
            'destinationPlace' =>  $destinos,
            'driver' => $drivers,
            'centers' => $centers,
            'cancellation' => $cancellation,
            'date' => $date,
            'appoinments' => $appoinments,
            'horas' => $ParamsTimeFilter,
            'totalFilter' => $totalFilter,

        ];
    }
}
