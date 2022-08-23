<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Appoinments;
use App\Components\FrontdeskTripsPlannerComponent;
use App\Components\FrontdeskConfirmedTripsPlannerComponent;
use App\Components\TripsPlannerComponent;
use App\Components\AppoinmentLogComponent;
use App\tmp_appoinments;
use App\ges_appoinments;
use App\Driver_assigments;
use App\Medical_centers;
use App\Patients;
use App\NotificationTrip;
use App\Coordenadas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDF;

class TripsController extends Controller
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

    public function index()
    {
        $appoinments = tmp_appoinments::all();
        $drivers = Driver_assigments::all();
        return view('trips', ['appoinments' => $appoinments, 'drivers' => $drivers]);
    }

    public function tripsok(Request $request)
    {
        NotificationTrip::truncate();
        ges_appoinments::truncate();
        $drivers = Driver_assigments::where(['Enable' => 1])->get();
        $appoinments = tmp_appoinments::all();
        $i = 0;
        foreach ($appoinments as $appoinment) {
            $dirorigin = $appoinment->AddressPatient;
            $dirdest = $appoinment->AddressDestination;
            $data = $this->getcoordenadas($dirorigin, $dirdest);
            //dd($data);
            $dist = $data['distance'];
            $distkm = $data['distancemt'] / 1000;
            $latorig = $data['latori'];
            $lngorig = $data['lngori'];
            $latdest = $data['latdest'];
            $lngdest = $data['lngdest'];
            $duration = $data['duration'];
            $durationmin = $data['durationseg'] / 60;
            $statusGoogle = $data['statusGoogle'];
            $distrange = '0';
            //Rango distance
            if ($dist > 0 and $dist <= 5) {
                $distrange = '1';
            }
            if ($dist > 5 and $dist <= 10) {
                $distrange = '2';
            }
            if ($dist > 10 and $dist <= 15) {
                $distrange = '3';
            }
            if ($dist > 15 and $dist <= 20) {
                $distrange = '4';
            }
            if ($dist > 20) {
                $distrange = '5';
            }
            //Asignacion de driver segun poligono del driver
            $xidriver = null;
            $xcolordriver = '#8686ac';
            $DriverName = '';
            foreach ($drivers as $driver) {
                if ($latorig <= $driver->north) {
                    $t1 = true;
                } else {
                    $t1 = false;
                }
                if ($latorig >= $driver->south) {
                    $t2 = true;
                } else {
                    $t2 = false;
                }
                if ($lngorig >= $driver->west) {
                    $t3 = true;
                } else {
                    $t3 = false;
                }
                if ($lngorig <= $driver->east) {
                    $t4 = true;
                } else {
                    $t4 = false;
                }
                if (($t1) and ($t2) and ($t3) and ($t4)) {
                    $xidriver = $driver->Id;
                    $xcolordriver = $driver->pcolor;
                    $DriverName = $driver->Driver;
                }
            }
            //creacion de viajes
            $i++;
            ges_appoinments::create([
                'IdMC' => $appoinment->IdMC,
                'IdOrder' => $i,
                'Time' => $appoinment->Time,
                'Date' => $appoinment->Date,
                'LastName' => $appoinment->LastName,
                'FirstName' => $appoinment->FirstName,
                'MiddleName' => $appoinment->MiddleName,
                'PatNumber' => $appoinment->PatNumber,
                'DOB' => $appoinment->DOB,
                'AddressPatient' => $appoinment->AddressPatient,
                'City' => $appoinment->City,
                'State' => $appoinment->State,
                'ZipCode' => $appoinment->ZipCode,
                'PhoneNumber' => $appoinment->PhoneNumber,
                'MobilNumber' => $appoinment->MobilNumber,
                'AddressDestination' => $appoinment->AddressDestination,
                'ConsultDestination' => $appoinment->ConsultDestination,
                'Driver' => $DriverName, //$appoinment->Driver,
                'TripType' => 'A', //$appoinment->TripType,
                'dist' => $dist,
                'distance_range' => $distrange,
                'distkm' => $distkm,
                'latorig' => $latorig,
                'lngorig' => $lngorig,
                'latdest' => $latdest,
                'lngdest' => $lngdest,
                'duration' => $duration,
                'durationmin' => $durationmin,
                'driver_id' => $xidriver,
                'drivercolor' => $xcolordriver,
                'statusGoogle' => $statusGoogle,
            ]);

            ges_appoinments::create([
                'IdMC' => $appoinment->IdMC,
                'IdOrder' => $i,
                'Time' => $appoinment->Time,
                'Date' => $appoinment->Date,
                'LastName' => $appoinment->LastName,
                'FirstName' => $appoinment->FirstName,
                'MiddleName' => $appoinment->MiddleName,
                'PatNumber' => $appoinment->PatNumber,
                'DOB' => $appoinment->DOB,
                'AddressPatient' => $appoinment->AddressDestination,
                'City' => $appoinment->City,
                'State' => $appoinment->State,
                'ZipCode' => $appoinment->ZipCode,
                'PhoneNumber' => $appoinment->PhoneNumber,
                'MobilNumber' => $appoinment->MobilNumber,
                'AddressDestination' => $appoinment->AddressPatient,
                'ConsultDestination' => $appoinment->ConsultDestination,
                'TripType' => 'B',
                'dist' => $dist,
                'distance_range' => $distrange,
            ]);
        }
        $drivers = Driver_assigments::all();
        $appoinments = ges_appoinments::where(['TripType' => 'A'])->get();
        $numproc = $appoinments->count();
        tmp_appoinments::truncate();
        return view('trips', ['appoinments' => $appoinments, 'drivers' => $drivers, 'numproc' => $numproc]);
    }


    public function tripsPlanner(Request $request)
    {
        // si se agregan otros viajes es recomendable usar el metodo factory, ya que las clases usan la misma interfaz
        $date = $request->get('date');
        if (!isset($date))
            $date = date('Y-m-d');

        $today = date('Y-m-d');
        $pickDay = explode("-", $date);
        $today = explode("-", $today);

        if ($today[2] == $pickDay[2] || $today[2]  < $pickDay[2]) {
            $workSheet = new TripsPlannerComponent();
            $json = $workSheet->getTripsPlanner($workSheet->getData($request));
            //print_r($json[7]->total);
            //dd($json);
            return view(
                'tripsplanner.tripsplanner',
                $json
            );
        } else {
            $workSheet = new AppoinmentLogComponent();
            $json = $workSheet->getSheetLog($workSheet->getData($request));
            return view(
                'logs.log_sheet',
                $json
            );
        }
    }

    public function frontdeskPlanner(Request $request)
    {
        // si se agregan otros viajes es recomendable usar el metodo factory, ya que las clases usan la misma interfaz

        $workSheet = new FrontdeskTripsPlannerComponent($request);

        $json = $workSheet->getTripsPlannerTypeB($workSheet->getDataTypeB($request));

        return view(
            'tripsplanner.frontdeskplanner',
            $json
        );
    }

    public function frontdeskPlannerConfirmed(Request $request)
    {
        // si se agregan otros viajes es recomendable usar el metodo factory, ya que las clases usan la misma interfaz

        $workSheet = new FrontdeskConfirmedTripsPlannerComponent($request);


        $json = $workSheet->getTripsPlannerTypeB($workSheet->getDataTypeBConfirmed($request));

        return view(
            'tripsplanner.frontdeskplannerconfirmed',
            $json
        );
    }
    public function assignDriverAppoinment(Request $request)
    {

        if ($request->ajax()) {

            $ges = ges_appoinments::find($request->gesid);
            $driver = Driver_assigments::find($request->driver_id);

            if (!empty($driver) && !empty($ges)) {

                $ges->Driver = $driver->Driver;
                $ges->driver_id = $request->driver_id;
                $ges->notified_driver = 0;
                $ges->save();
                $driver->Enable = 0;
                $driver->save();

                if ($ges->wasChanged()) {
                    $data = [
                        'name' => $driver->Driver,
                        'zone' => $driver->zones->Name,
                        'center' => $driver->center->NickName,


                    ];
                    return response($data, 200);
                } else {

                    $data = [
                        'message' => 'Error during driver assignment.'
                    ];
                    return response($data, 400);
                }
            } else {
                $data = [
                    'message' => 'error saving driver',
                    'data_request' => $request,
                    'ges' => $ges,
                    'driver' => $driver,
                ];
                return response($data, 400);
            }
        }
    }

    //function que realiza la acción de cambio de chofer y lo almacena en la BD
    public function changeDriverAppoinment(Request $request)
    {

        if ($request->ajax()) {

            $ges = ges_appoinments::findOrFail($request->gesid);
            $driver = Driver_assigments::findOrFail($request->driver_id);
            $driver_old = Driver_assigments::findOrFail($ges->driver_id);

            if (!empty($driver) && !empty($ges)) {

                $ges->Driver = $driver->Driver;
                $ges->driver_id = $request->driver_id;
                $ges->notified_driver = 0;
                $ges->save();
                $driver->Enable = 0;
                $driver->save();
                $driver_old->Enable = 1;
                $driver_old->save();

                if ($ges->wasChanged()) {


                    $data = [
                        'name' => $driver->Driver,
                        'zone' => $driver->zones->Name,
                        'center' => $driver->center->NickName,
                        'driver_name' => $driver->Driver,
                        'driver_enable' => $driver->Enable,
                        'driver_old_name' => $driver_old->Driver,
                        'driver_old_enable' => $driver_old->Enable,

                    ];
                    return response($data, 200);
                } else {

                    $data = [
                        'message' => 'Error Saving Driver -> ' . $driver->Driver .  " -> Can't be changed by Himself."
                    ];
                    return response($data, 400);
                }
            } else {
                $data = [
                    'message' => 'error saving driver -> ' . $driver->Driver
                ];
                return response($data, 400);
            }
        }
    }

    public function assignPickupConfirm($id)
    {
        $ges = ges_appoinments::find($id);
        $ges->ConfirmTrip = true;
        $ges->save();

        $notifTrip = new NotificationTrip;
        $notifTrip->ges_appoinments_id = $id;
        $notifTrip->message = 'Ready For Pick-Up.';
        $notifTrip->driver_id = $ges->driver_id;
        $notifTrip->save();

        return redirect()->route('frontdeskplanner');
    }

    public function assignPickupCancel($id)
    {
        $ges = ges_appoinments::find($id);
        $ges->ConfirmTrip = false;
        $ges->save();

        $notifTrip = new NotificationTrip;
        $notifTrip->ges_appoinments_id = $id;
        $notifTrip->message = 'The Pick-Up has been Cancelled.';
        $notifTrip->driver_id = $ges->driver_id;
        $notifTrip->save();

        return redirect()->route('frontdeskplannerconfirmed');
    }

    public function assingCancellationCode(Request $request)
    {

        if ($request->ajax()) {
            $ges = ges_appoinments::find($request->gesid);
            $ges->Cod_Cancell = $request->code;
            $ges->CD = Carbon::now()->toDateTimeString();
            $ges->save();

            if ($ges->wasChanged()) {
                return response($ges->toJson(), 200);
            } else {
                return response($ges->toJson(), 400);
            }
        }
    }

    public function assingUncancel(Request $request)
    {

        if ($request->ajax()) {
            $ges = ges_appoinments::find($request->id);
            $ges->Cod_Cancell = null;
            $ges->CD = null;
            $ges->save();

            if ($ges->wasChanged()) {
                return response($ges->toJson(), 200);
            } else {
                return response($ges->toJson(), 400);
            }
        }
    }


    public function editGesAppoinment(Request $request)
    {

        if ($request->ajax()) {

            $ges = ges_appoinments::find($request->id);
            $ges->Time = $request->appt_time;
            $ges->return_time = $request->return_time;
            $ges->MobilNumber = $request->appt_cell_phne;
            $ges->PhoneNumber = $request->appt_home_phone;
            $ges->AddressPatient = $request->pickup_address;
            $ges->AddressDestination = $request->dropoff_address;
            $ges->attention_type = $request->attetion_type;
            $ges->notes = $request->notes;
            $ges->tripType = $request->tripType;

            if (isset($request->requeriments)) {


                /*
                $req = "";
                $ges->special_requeriment = json_encode($request->requeriments);
                foreach ($request->requeriments as $key => $val) {
                    if (count($request->requeriments) - 1 == $key) {
                        $req = $req . $val;
                    } else {
                        $req = $req . $val . ", ";
                    }
                }*/
                $ges->format_requeriment = json_encode($request->requeriments);
                $ges->special_requeriment = json_encode($request->requeriments);
            }


            if ($request->inside == "false") {

                $ges->outside_center_name = $request->dest_center_name;
                $ges->outside_center_phone = $request->dest_center_phone;
                $ges->outside_doctor_resource = $request->appt_doctor_resource;
                $ges->outside_motive = $request->appt_motive;
                $ges->outside_motive_details = $request->appt_motive_details;
            }
            $ges->save();



            return response($ges->toJson(), 200);
        }
    }

    public function create(Request $request)
    {

        $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');
        $centersInfo = DB::table('medical_centers')->get();
        $patient = DB::table('patients')->where('Id', $request->id)->first();
        return view('tripsplanner.newtrip', ['centers' => $centers, 'patient' => $patient, 'centersInfo' => $centersInfo]);
    }

    public function store(Request $request)
    {

        $gesAppoinment = new ges_appoinments;
        $patient = DB::table('patients')->where('Id', $request->patientId)->first();
        //dd($patient);

        $names = explode(" ",  $patient->Names);
        switch (count($names)) {

            case 1:
                $gesAppoinment->FirstName = $names[0];
                break;
            case 2:
                $gesAppoinment->FirstName = $names[0];
                $gesAppoinment->LastName = $names[1];
                break;
            case 3:
                $gesAppoinment->FirstName = $names[0];
                $gesAppoinment->MiddleName = $names[1];
                $gesAppoinment->LastName = $names[2];
                break;
        }
        $gesAppoinment->IdMC = $patient->Id;
        $gesAppoinment->DOB =  $patient->BOD;
        $gesAppoinment->notes = $request->notes;
        $gesAppoinment->attention_type = $gesAppoinment->attetionType($request->visittype);

        if (strcmp($request->IdMedicalC, "NC") == 0) {
            $medicalCenter = new Medical_centers;
            $medicalCenter->Name = $request->centername;
            $medicalCenter->NickName = $request->nickname;
            $medicalCenter->NumberPhone = $request->phoneone;
            $medicalCenter->NumberPhone1 = $request->phoneone;
            $medicalCenter->NumberPhone2 = $request->phonetwo;
            $medicalCenter->FaxNumber = $request->faxnumber;
            $medicalCenter->Email = $request->emailcenter;
            $medicalCenter->AddressMedicalC = $request->centerAddress;
            $medicalCenter->save();
        }
        $now = Carbon::now();
        $gesAppoinment->created_at = $now;
        $gesAppoinment->updated_at = $now;

        if ($request->servicetype == 1 || $request->servicetype == 3) {
            $gesAppoinment->TripType = $gesAppoinment->serviceType($request->servicetype);
            $gesAppoinment->AddressPatient = $patient->PatientAddress;
            $gesAppoinment->AddressDestination = $request->IdMedicalC;
            $date = explode("T", $request->pickuptime);
            $gesAppoinment->Date = $date[0];
            $gesAppoinment->Time = $date[1];
            //dd( $gesAppoinment);
            $gesAppoinment->save();
        } elseif ($request->servicetype == 2) {
            $gesAppoinment->TripType = $gesAppoinment->serviceType($request->servicetype);
            $gesAppoinment->AddressPatient = $request->IdMedicalC;
            $gesAppoinment->AddressDestination = $patient->PatientAddress;
            $date = explode("T", $request->pickupcenter);
            $gesAppoinment->Date = $date[0];
            $gesAppoinment->Time = $date[1];
            $gesAppoinment->save();
        }

        if ($request->servicetype == 3) {
            $drofGesAppoinment = $gesAppoinment->replicate();
            $drofGesAppoinment->TripType = $gesAppoinment->serviceType(2);
            $drofGesAppoinment->AddressPatient = $request->IdMedicalC;
            $drofGesAppoinment->AddressDestination = $patient->PatientAddress;
            $date = explode("T", $request->pickupcenter);
            $drofGesAppoinment->Date = $date[0];
            $drofGesAppoinment->Time = $date[1];
            $drofGesAppoinment->save();
        }

        return redirect()->route('getpatients', ['patientname' =>  $names[0]]);
    }

    public function reportInside(Request $request)
    {
        /*
        $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');
        $appoinments = ges_appoinments::where('TripType', '=', 'A')
            ->whereIn('AddressDestination', $centers->keys())
            ->orderBy('Time', 'ASC')
            ->get();
*/
        $appoinments =  ges_appoinments::join('medical_centers', 'ges_appoinments.AddressDestination', '=', 'medical_centers.AddressMedicalC')
            ->where('TripType', '=', 'A')
            ->orderBy('Time', 'ASC')
            ->get();

        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');

        $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
        foreach ($appoinments as $key => $value) {
            if (isset($value->IdMC) == false) {
                $appoinments[$key]['IdMC'] = $patientDummy->Id;
            }
        }

        $pdf = PDF::loadView(
            'reports.planning',
            [
                'appoinments' => $appoinments,
                'date' => $date,
                'trip' => 'Inside'
            ]
        );
        //->setPaper('a4', 'landscape');
        //$pdf->getDomPDF()->set_option("enable_php", true);
        return $pdf->stream();
        /*
        $pdf = \App::make('dompdf.wrapper');
        //$pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView('reports.planning',
            [
                'appoinments' => $appoinments,
                'date' => $date,
                'trip' => 'Inside'
            ]
            );
        return $pdf->stream();*/
    }

    public function reportOutside(Request $request)
    {
        $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');
        $appoinments = ges_appoinments::where('TripType', '=', 'A')
            ->whereNotIn('AddressDestination', $centers->keys())
            ->orderBy('Time', 'ASC')
            ->get();
        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');

        $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
        foreach ($appoinments as $key => $value) {
            if (isset($value->IdMC) == false) {
                $appoinments[$key]['IdMC'] = $patientDummy->Id;
            }
        }


        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView(
            'reports.planning',
            [
                'appoinments' => $appoinments,
                'date' => $date,
                'trip' => 'Outside'
            ]
        )->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function reportTripList(Request $request)
    {

        if (isset($request->center)  || isset($request->orderby)   || isset($request->date)) {

            $center = isset($request->center) ? urldecode($request->center)  : null;
            $filterDate = isset($request->date) ? $request->date : null;

            if (isset($request->orderby) && $request->orderby == 1) {
                $orderby = "Time";
            } elseif (isset($request->orderby) && $request->orderby == 2) {
                $orderby = "LastName";
            } else {
                $orderby = "Time";
            }

            $appoinments = Appoinments::where('TripType', '=', 'A')
                ->date($filterDate)
                ->center($center)
                ->orderBy($orderby, 'ASC')
                ->get();

            $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
            foreach ($appoinments as $key => $value) {
                if (isset($value->IdMC) == false) {
                    $appoinments[$key]['IdMC'] = $patientDummy->Id;
                }
            }
        } else {
            $appoinments = ges_appoinments::where('TripType', '=', 'A')
                ->orderBy('Time', 'ASC')
                ->get();

            $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
            foreach ($appoinments as $key => $value) {
                if (isset($value->IdMC) == false) {
                    $appoinments[$key]['IdMC'] = $patientDummy->Id;
                }
            }
        }

        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');





        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView(
            'reports.transportationlist',
            [
                'appoinments' => $appoinments,
                'date' => $date,
                'trip' => 'Transportation List'
            ]
        )->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function reportDrivers(Request $request)
    {

        if (isset($request->center)  || isset($request->orderby)   || isset($request->date)) {

            $center = isset($request->center) ? urldecode($request->center)  : null;
            $filterDate = isset($request->date) ? $request->date : null;

            if (isset($request->orderby) && $request->orderby == 1) {
                $orderby = "Time";
            } elseif (isset($request->orderby) && $request->orderby == 2) {
                $orderby = "LastName";
            } else {
                $orderby = "Time";
            }
            /*
            $appoinments = Appoinments::where('TripType', '=', 'A')
            ->where('Driver', '<>', null)
            ->date($filterDate)
            ->center($center)
            ->orderBy($orderby, 'ASC')
            ->get();

            $drivers = Appoinments::select(DB::raw('count(*) as driver_trips, Driver'))
            ->where('Driver', '<>', null)
            ->where('TripType', '=', 'A')
            ->date($filterDate)
            ->center($center)
            ->groupBy('Driver')
            ->orderBy('Driver', 'ASC')
            ->get();
            */


            $appoinments = Appoinments::where('TripType', '=', 'A')
                ->where('driver_id', '<>', null)
                ->date($filterDate)
                ->center($center)
                ->orderBy($orderby, 'ASC')
                ->get();

            $drivers = Appoinments::select(DB::raw('count(*) as driver_trips, driver_id'))
                ->where('driver_id', '<>', null)
                ->where('TripType', '=', 'A')
                ->date($filterDate)
                ->center($center)
                ->groupBy('driver_id');


            $drivers = DB::table('driver_assigments')
                ->select('drivers.driver_trips', 'drivers.driver_id', 'driver_assigments.Driver')
                ->joinSub($drivers, 'drivers', function ($join) {
                    $join->on('driver_assigments.Id', '=', 'drivers.driver_id');
                })->get();

            $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
            foreach ($appoinments as $key => $value) {
                if (isset($value->IdMC) == false) {
                    $appoinments[$key]['IdMC'] = $patientDummy->Id;
                }
            }
        } else {

            $appoinments = ges_appoinments::where('TripType', '=', 'A')
                ->where('driver_id', '<>', null)
                //->orderBy('driver_id', 'ASC')
                ->orderBy('Time', 'ASC')
                ->get();

            //$drivers =  ges_appoinments::select('Driver')->distinct()->where('Driver','<>' ,null)->get();
            $drivers = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips, driver_id'))
                ->where('driver_id', '<>', null)
                ->where('TripType', '=', 'A')
                ->groupBy('driver_id');
            //->orderBy('driver_id', 'ASC')
            //->get();

            $drivers = DB::table('driver_assigments')
                ->select('drivers.driver_trips', 'drivers.driver_id', 'driver_assigments.Driver')
                ->joinSub($drivers, 'drivers', function ($join) {
                    $join->on('driver_assigments.Id', '=', 'drivers.driver_id');
                })->get();


            $patientDummy = Patients::where('Email', '=', 'patientzero@yomail.com')->first();
            foreach ($appoinments as $key => $value) {
                if (isset($value->IdMC) == false) {
                    $appoinments[$key]['IdMC'] = $patientDummy->Id;
                }
            }
        }

        $totalTrips = count($appoinments);
        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');

        /*
        $pdf = PDF::loadView(
            'reports.driverreport', ['appoinments' => $appoinments, 'drivers' => $drivers, 'date' =>   $date])
            ->setPaper('a4', 'landscape');
        $pdf->getDomPDF()->set_option("enable_php", true);
        return $pdf->stream();*/

        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView(
            'reports.driverreport',
            [
                'appoinments' => $appoinments,
                'drivers' => $drivers,
                'date' =>   $date,
                'total' =>  $totalTrips

            ]
        )->setPaper('a4', 'landscape');
        return $pdf->stream('invoice.pdf');
    }

    public function reportPickstatus(Request $request)
    {

        $trip = $request->trip;
        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');

        if (strcmp($trip, 'A') == 0) {
            $description = 'Monitoring Pickup Trips';
        } else {
            $description = 'Monitoring Return Trips';
        }

        $pendings = ges_appoinments::where([
            ['TripType', '=', $trip],
            ['driver_id', '<>', null],
            ['OB', '=', null],
            ['RP', '=', null],
            ['CD', '=', null],
            ['OO', '=', null],
        ])->orderBy('Driver', 'ASC')->orderBy('Time', 'ASC')->get();

        $onWay = ges_appoinments::where([
            ['TripType', '=', $trip],
            ['driver_id', '<>', null],
            ['OB', '<>', null]
        ])->orderBy('Driver', 'ASC')->orderBy('Time', 'ASC')->get();

        $completed = ges_appoinments::where([
            ['TripType', '=', $trip],
            ['driver_id', '<>', null],
            ['OB', '<>', null],
            ['RP', '<>', null]
        ])->orderBy('Driver', 'ASC')->orderBy('Time', 'ASC')->get();

        $cancelled = ges_appoinments::where([
            ['TripType', '=', $trip],
            ['driver_id', '<>', null],
            ['CD', '<>', null],
        ])->orderBy('Driver', 'ASC')->orderBy('Time', 'ASC')->get();

        $totalTrips =  $pendings->count() +  $onWay->count() +  $completed->count() +  $cancelled->count();
        /*
        $pdf = PDF::loadView(
            'reports.reporttripstatus',
            [
                'pendings' => $pendings, 'onWay' => $onWay, 'completed' => $completed,
                'cancelled' => $cancelled, 'totalTrips' => $totalTrips, 'date' =>   $date,
                'description' => $description
            ]
        )->setPaper('a4', 'landscape');*/

        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView(
            'reports.reporttripstatus',
            [
                'pendings' => $pendings, 'onWay' => $onWay, 'completed' => $completed,
                'cancelled' => $cancelled, 'totalTrips' => $totalTrips, 'date' =>   $date,
                'description' => $description
            ]
        )->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function allTripsInfo(Request $request)
    {

        if (isset($request->center)  || isset($request->orderby)   || isset($request->date)) {

            $center = isset($request->center) ? urldecode($request->center)  : null;
            $filterDate = isset($request->date) ? $request->date : null;

            if (isset($request->orderby) && $request->orderby == 1) {
                $orderby = "Time";
            } elseif (isset($request->orderby) && $request->orderby == 2) {
                $orderby = "LastName";
            } else {
                $orderby = "Time";
            }

            $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');

            $appoinmentsInside = Appoinments::select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where('TripType', '=', 'A')
                ->where('Driver', '<>', null)
                ->date($filterDate)
                ->centers($center, $centers)
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('AddressDestination', 'ASC')
                ->get();


            $appoinmentsInsideComplete = Appoinments::select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['OB', '<>', null],
                    ['RP', '<>', null]
                ])
                ->date($filterDate)
                ->centers($center, $centers)
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('AddressDestination', 'ASC')
                ->get();



            $appoinmentsInsideCancel = Appoinments::select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['CD', '<>', null]
                ])
                ->date($filterDate)
                ->centers($center, $centers)
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('AddressDestination', 'ASC')
                ->get();

            $totalTrips =  Appoinments::where([
                ['TripType', '=', 'A'],
                ['Driver', '<>', null]
            ])
                ->date($filterDate)
                ->centers($center, $centers)
                ->count();

            $totalTripsComplete =  Appoinments::where([
                ['TripType', '=', 'A'],
                ['Driver', '<>', null],
                ['OB', '<>', null],
                ['RP', '<>', null]
            ])
                ->date($filterDate)
                ->centers($center, $centers)
                ->count();

            $totalTripsCancel = Appoinments::where([
                ['TripType', '=', 'A'],
                ['Driver', '<>', null],
                ['CD', '<>', null]

            ])
                ->date($filterDate)
                ->centers($center, $centers)
                ->count();

            $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');

            /*
                $pdf = PDF::loadView(
                    'reports.reporttripinfo',
                    [
                        'centers' => $centers, 'inside' => $appoinmentsInside, 'insideComplete' => $appoinmentsInsideComplete,
                        'insideCancel' => $appoinmentsInsideCancel,
                        'totalTrips' => $totalTrips,
                        'totalTripsComplete' => $totalTripsComplete,
                        'date' => $date,
                        'filter' => "true",
                        'totalTripsCancel' => $totalTripsCancel
                    ]
                )->setPaper('a4', 'landscape');
            */
            $pdf = \App::make('dompdf.wrapper');
            $pdf->getDomPDF()->set_option("enable_php", true);
            $pdf->loadView(
                'reports.reporttripinfo',
                [
                    'centers' => $centers, 'inside' => $appoinmentsInside, 'insideComplete' => $appoinmentsInsideComplete,
                    'insideCancel' => $appoinmentsInsideCancel,
                    'totalTrips' => $totalTrips,
                    'totalTripsComplete' => $totalTripsComplete,
                    'date' => $date,
                    'filter' => "true",
                    'totalTripsCancel' => $totalTripsCancel
                ]
            )->setPaper('a4', 'landscape');
            return $pdf->stream();

            return $pdf->stream();
        } else {

            $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');

            $appoinmentsInside = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where('TripType', '=', 'A')
                ->whereIn('AddressDestination', $centers->keys())
                ->where('Driver', '<>', null)
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('AddressDestination', 'ASC')
                ->get();


            $appoinmentsInsideComplete = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['OB', '<>', null],
                    ['RP', '<>', null]
                ])
                ->whereIn('AddressDestination', $centers->keys())
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('AddressDestination', 'ASC')
                ->get();



            $appoinmentsInsideCancel = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['CD', '<>', null]
                ])
                ->whereIn('AddressDestination', $centers->keys())
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('AddressDestination', 'ASC')
                ->get();

            $appoinmentsOutside = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips,Driver'))
                ->where('TripType', '=', 'A')
                ->whereNotIn('AddressDestination', $centers->keys())
                ->where('Driver', '<>', null)
                ->groupBy('Driver')
                ->orderBy('Driver', 'ASC')
                ->get();

            $appoinmentsOutsideComplete = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['OB', '<>', null],
                    ['RP', '<>', null]
                ])
                ->whereNotIn('AddressDestination', $centers->keys())
                ->groupBy('Driver')
                ->orderBy('Driver', 'ASC')
                ->get();
            //dd($appoinmentsOutsideComplete);
            $appoinmentsOutsideCancel = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['CD', '<>', null]
                ])
                ->whereNotIn('AddressDestination', $centers->keys())
                ->groupBy('Driver')
                ->orderBy('Driver', 'ASC')
                ->get();

            $totalTrips = DB::table('ges_appoinments')
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null]
                ])->count();

            $totalTripsComplete = DB::table('ges_appoinments')
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['OB', '<>', null],
                    ['RP', '<>', null]
                ])->count();

            $totalTripsCancel = DB::table('ges_appoinments')
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                    ['CD', '<>', null]
                ])->count();
        }

        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');

        /*
        $pdf = PDF::loadView(
            'reports.reporttripinfo',
            [
                'centers' => $centers, 'inside' => $appoinmentsInside, 'insideComplete' => $appoinmentsInsideComplete,
                'insideCancel' => $appoinmentsInsideCancel, 'outsides' => $appoinmentsOutside, 'outsideComplete' => $appoinmentsOutsideComplete,
                'outsideCancel' => $appoinmentsOutsideCancel, 'totalTrips' => $totalTrips,
                'totalTripsComplete' => $totalTripsComplete, 'totalTripsCancel' => $totalTripsCancel, 'date' => $date,
                'filter' => "false"
            ]
        )->setPaper('a4', 'landscape');
        */
        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView(
            'reports.reporttripinfo',
            [
                'centers' => $centers, 'inside' => $appoinmentsInside, 'insideComplete' => $appoinmentsInsideComplete,
                'insideCancel' => $appoinmentsInsideCancel, 'outsides' => $appoinmentsOutside, 'outsideComplete' => $appoinmentsOutsideComplete,
                'outsideCancel' => $appoinmentsOutsideCancel, 'totalTrips' => $totalTrips,
                'totalTripsComplete' => $totalTripsComplete, 'totalTripsCancel' => $totalTripsCancel, 'date' => $date,
                'filter' => "false"
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->stream();
    }

    public function tripsDistance(Request $request)
    {
        $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');

        if (isset($request->center)  || isset($request->orderby)   || isset($request->date)) {

            $center = isset($request->center) ? urldecode($request->center)  : null;
            $filterDate = isset($request->date) ? $request->date : null;

            if (isset($request->orderby) && $request->orderby == 1) {
                $orderby = "Time";
            } elseif (isset($request->orderby) && $request->orderby == 2) {
                $orderby = "LastName";
            } else {
                $orderby = "Time";
            }

            $tripsRadius = Appoinments::select(DB::raw('count(*) as driver_trips, AddressDestination,distance_range'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['distance_range', '<>', null],
                ])
                ->date($filterDate)
                ->centers($center, $centers)
                ->groupBy('AddressDestination', 'distance_range')
                ->orderBy('AddressDestination', 'ASC')
                ->get();
        } else {

            $tripsRadius = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips, AddressDestination,distance_range'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['distance_range', '<>', null],
                ])
                ->whereIn('AddressDestination', $centers->keys())
                ->groupBy('AddressDestination', 'distance_range')
                ->orderBy('AddressDestination', 'ASC')
                ->get();
        }

        $totalTrips = 0;
        foreach ($tripsRadius as $radius) {
            $totalTrips =  $totalTrips + $radius->driver_trips;
        }


        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');
        /*
        $pdf = PDF::loadView(
            'reports.reporttripradius',
            ['centers' => $centers, 'tripsRadius' => $tripsRadius, 'date' => $date, 'totalTrips' => $totalTrips]
        )->setPaper('a4', 'landscape');
        */
        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView(
            'reports.reporttripradius',
            ['centers' => $centers, 'tripsRadius' => $tripsRadius, 'date' => $date, 'totalTrips' => $totalTrips]
        )->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function scheduledTrips(Request $request)
    {

        $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');

        if (isset($request->center)  || isset($request->orderby)   || isset($request->date)) {

            $center = isset($request->center) ? urldecode($request->center)  : null;
            $filterDate = isset($request->date) ? $request->date : null;

            if (isset($request->orderby) && $request->orderby == 1) {
                $orderby = "Time";
            } elseif (isset($request->orderby) && $request->orderby == 2) {
                $orderby = "LastName";
            } else {
                $orderby = "Time";
            }

            $scheduledTrips = Appoinments::select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                ])
                ->date($filterDate)
                ->centers($center, $centers)
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('Driver', 'ASC')
                ->get();
        } else {
            $scheduledTrips = DB::table('ges_appoinments')
                ->select(DB::raw('count(*) as driver_trips, AddressDestination,Driver'))
                ->where([
                    ['TripType', '=', 'A'],
                    ['Driver', '<>', null],
                ])
                ->whereIn('AddressDestination', $centers->keys())
                ->groupBy('AddressDestination', 'Driver')
                ->orderBy('Driver', 'ASC')
                ->get();
        }


        $totalTrips = 0;
        foreach ($scheduledTrips as $trips) {
            $totalTrips =  $totalTrips + $trips->driver_trips;
        }

        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');
        /*
        $pdf = PDF::loadView(
            'reports.scheduledstrips',
            ['centers' => $centers, 'scheduleds' => $scheduledTrips, 'date' => $date, 'totalTrips' => $totalTrips]
        )->setPaper('a4', 'landscape');
        */
        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView(
            'reports.scheduledstrips',
            ['centers' => $centers, 'scheduleds' => $scheduledTrips, 'date' => $date, 'totalTrips' => $totalTrips]
        )->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function perfomanceTrips()
    {

        $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');

        $pp = ges_appoinments::where([
            ['TripType', '=', 'A'],
            ['Driver', '<>', null],
            ['OB', '=', null],
            ['RP', '=', null],
            ['CD', '=', null],
            ['OO', '=', null],
        ])->whereIn('AddressDestination', $centers->keys())->get();

        $op = ges_appoinments::where([
            ['TripType', '=', 'A'],
            ['Driver', '<>', null],
            ['OB', '<>', null]
        ])->whereIn('AddressDestination', $centers->keys())->get();

        $cp = ges_appoinments::where([
            ['TripType', '=', 'A'],
            ['Driver', '<>', null],
            ['OB', '<>', null],
            ['RP', '<>', null]
        ])->whereIn('AddressDestination', $centers->keys())->get();

        $ccp = ges_appoinments::where([
            ['TripType', '=', 'A'],
            ['Driver', '<>', null],
            ['CD', '<>', null],
        ])->whereIn('AddressDestination', $centers->keys())->get();


        $pr = ges_appoinments::where([
            ['TripType', '=', 'B'],
            ['Driver', '<>', null],
            ['OB', '=', null],
            ['RP', '=', null],
            ['CD', '=', null],
            ['OO', '=', null],
        ])->whereIn('AddressPatient', $centers->keys())->get();

        $or = ges_appoinments::where([
            ['TripType', '=', 'B'],
            ['Driver', '<>', null],
            ['OB', '<>', null]
        ])->whereIn('AddressPatient', $centers->keys())->get();

        $cr = ges_appoinments::where([
            ['TripType', '=', 'B'],
            ['Driver', '<>', null],
            ['OB', '<>', null],
            ['RP', '<>', null]
        ])->whereIn('AddressPatient', $centers->keys())->get();

        $ccr = ges_appoinments::where([
            ['TripType', '=', 'B'],
            ['Driver', '<>', null],
            ['CD', '<>', null],
        ])->whereIn('AddressPatient', $centers->keys())->get();


        $totalTrips = $or->count() + $cr->count() + $ccr->count() +  $pr->count();
        $tripsCompleted = $cr->count();
        $date = Carbon::now('America/Caracas')->isoFormat('MMMM Do YYYY, h:mm:ss a');
        $collection = collect([
            'pickup' => [
                [
                    'pending' => $pp->count(),
                    'on way' => $op->count(),
                    'completed' => $cp->count(),
                    'cancelled' => $ccp->count(),
                ],
            ],
            'return' => [
                [
                    'pending' => $pr->count(),
                    'on way' => $or->count(),
                    'completed' => $cr->count(),
                    'cancelled' => $ccr->count(),
                ],
            ],
        ]);

        return view('reports.reportperformancetrips', [
            'total' =>  $totalTrips,
            'completed' => $tripsCompleted,
            'date' => $date,
            'performace' => $collection
        ]);
    }

    public function reportsFilter(Request $request)
    {

        $centers = DB::table('medical_centers')->pluck('Name', 'AddressMedicalC');

        return view('reports.reportsfilter', [
            'route' =>  $request->route,
            'centers' => $centers
        ]);
    }

    public function controlCenter(Request $request)
    {

        $controlCenter = new TripsPlannerComponent();
        $json = $controlCenter->getControlCenter($controlCenter->getData($request));
        return view(
            'controlcenter.tripsplanner',
            $json
        );
    }

    public function getcoordenadas($dori, $ddest)
    {
        $ApiKEY = env('APIGOOGLEKEY');

        //Diretion origin
        $direxist = Coordenadas::where(['dirhash' => md5($dori)])->get();
        if ($direxist->count() > 0) {
            /*print_r(md5($dori).'<br>dir:');
            print_r($dori.'<br>count: ');
            print_r($direxist->count().'<br>hasBD:');
            print_r($direxist[0]['address']);
            dd($direxist);*/
            $latori = $direxist[0]['latitud'];
            $lngori = $direxist[0]['longitud'];
        } else {
            $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($dori) . '&key=' . $ApiKEY);
            $geo = json_decode($geo, true);
            if ($geo['status'] = 'OK') {
                $latori = $geo['results'][0]['geometry']['location']['lat'];
                $lngori = $geo['results'][0]['geometry']['location']['lng'];
                Coordenadas::create([
                    'dirhash' => md5($dori),
                    'address' => $dori,
                    'latitud' => $latori,
                    'longitud' => $lngori,
                ]);
            } else {
                $latori = $lngori = null;
            }
        }



        //Diretion destination
        $direxist = Coordenadas::where(['dirhash' => md5($ddest)])->get();
        if ($direxist->count() > 0) {
            $latdest = $direxist[0]['latitud'];
            $lngdest = $direxist[0]['longitud'];
        } else {
            $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($ddest) . '&key=' . $ApiKEY);
            $geo = json_decode($geo, true);
            if ($geo['status'] = 'OK') {
                $latdest = $geo['results'][0]['geometry']['location']['lat'];
                $lngdest = $geo['results'][0]['geometry']['location']['lng'];
                Coordenadas::create([
                    'dirhash' => md5($ddest),
                    'address' => $ddest,
                    'latitud' => $latdest,
                    'longitud' => $lngdest,
                ]);
            } else {
                $latdest = $lngdest = null;
            }
        }

        //Distance
        $orig = $latori . ',' . $lngori;
        $dest = $latdest . ',' . $lngdest;
        $geo = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=' . urlencode($orig) . '&destinations=' . urlencode($dest) . '&key=' . $ApiKEY);
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {
            // Obtener los valores
            $distance = $geo['rows'][0]['elements'][0]['distance']['text'];
            $distancemt = $geo['rows'][0]['elements'][0]['distance']['value'];
            $duration = $geo['rows'][0]['elements'][0]['duration']['text'];
            $durationseg = $geo['rows'][0]['elements'][0]['duration']['value'];
        } else {
            $distance = 0;
            $distancemt = 0;
            $duration = 0;
            $durationseg = 0;
        }
        $statusGoogle =  $geo['status'];
        $data = ["latori" => $latori, "lngori" => $lngori, "latdest" => $latdest, "lngdest" => $lngdest, "distance" => $distance, "distancemt" => $distancemt, "duration" => $duration, "durationseg" => $durationseg, "statusGoogle" => $statusGoogle];
        return $data;
    }
}
