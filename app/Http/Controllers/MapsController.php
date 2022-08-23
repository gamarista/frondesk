<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ges_appoinments;
use App\Medical_centers;
use App\Driver_assigments;
use Illuminate\Support\Facades\DB;

class MapsController extends Controller
{
    public function genrides()
    {
        $drivers = Driver_assigments::where(['Enable' => 1])->get();
        $medicalc = Medical_centers::All();
        return view('mapridesgen', ["medicalc" => $medicalc, "drivers" => $drivers]);
    }

    public function frontdeskgenrides()
    {
        $drivers = Driver_assigments::where(['Enable' => 1])->get();
        $medicalc = Medical_centers::All();
        return view('mapridesgenfd', ["medicalc" => $medicalc, "drivers" => $drivers]);
    }

    public function genroutes()
    {
        //$medicalc=Medical_centers::All();
        //$drivers=Driver_assigments::All();
        $drivers = Driver_assigments::where(['Enable' => 1])->get();
        $medicalc = ges_appoinments::groupBy('AddressDestination', 'latdest', 'lngdest', 'TripType')
            ->selectRaw('AddressDestination,latdest,lngdest,count(TripType)')
            ->where('TripType', '=', 'A')
            ->get();
        return view('mapsroute', ["medicalc" => $medicalc, "drivers" => $drivers]);
    }

    public function mapsgenroutes()
    {
        $medicalc = ges_appoinments::groupBy('AddressDestination', 'latdest', 'lngdest', 'TripType')
            ->selectRaw('AddressDestination,latdest,lngdest,count(TripType)')
            ->where('TripType', '=', 'A')
            ->get();
        //dd($medicalc);
        $drivers = Driver_assigments::All();
        return view('mapsgenroutes', ["medicalc" => $medicalc, "drivers" => $drivers]);
    }

    public function showroutes(Request $request)
    {
        $xOrig = $request->wOrig;
        $xDest = $request->wDest;
        $xDate = $request->wDate;
        $xTime = $request->wTime;
        $idDriver = $request->idDriver;
        //$routes=$this->GetAPI("getroutesmaps/".$xDate."/".$xTime,"GET","");
        //dd($routes);
        $strName = "";
        $strDirName = "";

        $horaini = $xTime;
        $xhora = strtotime($xTime) + 3600;
        $horafin = date('H:i', $xhora);
        $routes = ges_appoinments::whereBetween('Time', [$horaini, $horafin])
            ->where(['Date' => $xDate])->where(['Driver' => $idDriver])->where(['TripType' => 'A'])->get();

        //        $routes=ges_appoinments::where(['Date'=>$xDate])->where(['Time'=>$horaini])->where(['Driver'=>$idDriver])->where(['TripType'=>'A'])->get();

        echo $horaini . " - " . $horafin . " - " . $xDate . " - " . $idDriver;
        //dd($routes);
        if ($routes->count() > 0) {
            $result = json_decode($routes);
            //dd($result);
            foreach ($result as $dato) {
                $strName = $strName . "'" . $dato->LastName . "',";
                $strDirName = $strDirName . "'" . $dato->AddressPatient . "',";
            }
            $medicalc = Medical_centers::All();
            return view('gmaps.mapsroutes', ["medicalc" => $medicalc, "routes" => $strName, "dirpat" => $strDirName, "fecha" => $xDate, "hora" => $xTime, "origin" => $xOrig, "dest" => $xDest]);
        } else {
            $msge = "There are no trips with the selected data";
            $medicalc = Medical_centers::All();
            $drivers = Driver_assigments::All();
            return view('gmaps.mapsrouteserror', ["statuse" => $msge]);
        }
    }


    public function showroutesd(Request $request)
    {
        //$xOrig=$request->wOrig;
        $xDest = $request->wDest;
        $xDate = $request->wDate;
        $xTimeB = $request->wTimeB;
        $xTimeE = $request->wTimeE;
        $idDriver = trim($request->idDriver);
        //$xAssignDriver=$request->wAssignDriver;

        $strName = "";
        $strDirName = "";
        $wOrig = "";
        $maxTime = 0;
        //$horaini=$xTimeB;
        //$xhoraini=strtotime($xTimeB)+3600;
        //$horaend=$xTimeE;
        //$xhora=strtotime($xTimeE)+3600;
        //$horafin=date('H:i', $xhora);
        if ($request->wAssignDriver) {

            $DriverId = Driver_assigments::where('Driver', '=', $idDriver)->first();
            $genroutes = ges_appoinments::where('Date', '=', $xDate)
                ->whereTime('Time', '>=', $xTimeB)
                ->whereTime('Time', '<=', $xTimeE . ":01")
                ->where('AddressDestination', '=', $xDest)
                ->where('TripType', '=', 'A')
                ->update(['Driver' => $idDriver, 'driver_id' => $DriverId->Id]);
        }

        $routes = DB::table('ges_appoinments')
            //->where('Time', '=', $horaini)
            //->whereBetween('Time', [$xTimeB, $xTimeE])
            //->selectRaw('id,Time,TIME_FORMAT(Time,"%H") as xTime')
            ->where('Date', '=', $xDate)
            ->whereTime('Time', '>=', $xTimeB)
            ->whereTime('Time', '<=', $xTimeE . ":00:01")
            ->where('AddressDestination', '=', $xDest)
            ->where('Driver', '=', $idDriver)
            ->where('TripType', '=', 'A')
            ->get();

        if ($routes->count() > 0) {
            $result = json_decode($routes);
            //dd($result);
            foreach ($result as $dato) {
                $strName = $strName . "'" . $dato->FirstName . "',";
                $strDirName = $strDirName . "'" . $dato->AddressPatient . "',";
                //$wOrig=$wOrig."'".$dato->AddressPatient."',";
                if ($dato->durationmin > $maxTime) {
                    $maxTime = $dato->durationmin;
                    $wOrig = $dato->AddressPatient;
                }
            }
            $medicalc = Medical_centers::All();
            $xTimeB = date('g:i a', strtotime($xTimeB));
            $xTimeE = date('g:i a', strtotime($xTimeE));
            return view('gmaps.mapsroutes', ["medicalc" => $medicalc, "routes" => $strName, "dirpat" => $strDirName, "fecha" => $xDate, "horaini" => $xTimeB, "horaend" => $xTimeE, "origin" => $wOrig, "dest" => $xDest, "driver" => $idDriver]);
        } else {
            $msge = "There are no trips with the selected data";
            $medicalc = Medical_centers::All();
            $drivers = Driver_assigments::All();
            return view('gmaps.mapsrouteserror', ["statuse" => $msge]);
            /*   $medicalc = ges_appoinments::groupBy('AddressDestination','latdest','lngdest','TripType')
        ->selectRaw('AddressDestination,latdest,lngdest,count(TripType)')
        ->where('TripType', '=', 'A')
        ->get();
    //dd($medicalc);
    $drivers=Driver_assigments::All();
    return view('mapsgenroute', ["medicalc"=>$medicalc,"drivers"=>$drivers,"msge"=>$msge]);
*/
        }
    }

    //asignar rutas gen route
    public function genroutesd(Request $request)
    {
        $xDest = $request->wDest;
        $xDate = $request->wDate;
        $xTimeB = $request->wTimeB;
        $xTimeE = $request->wTimeE;
        $idDriver = trim($request->idDriver);
        $strName = "";
        $strDirName = "";
        $wOrig = "";
        $maxTime = 0;
        $routes = ges_appoinments::where('Date', '=', $xDate)
            ->whereTime('Time', '>=', $xTimeB)
            ->whereTime('Time', '<=', $xTimeE . ":00:01")
            ->where('AddressDestination', '=', $xDest)
            ->where('TripType', '=', 'A')
            ->update(['Driver' => $$idDriver]);

        /*    $routes = DB::table('ges_appoinments')
       ->where('Date', '=', $xDate)
       ->whereTime('Time', '>=', $xTimeB)
       ->whereTime('Time', '<=', $xTimeE.":00:01")
       ->where('AddressDestination', '=', $xDest)
       ->where('Driver', '=', $idDriver)
       ->where('TripType','=','A')
       ->get();        */

        if ($routes->count() > 0) {
            $result = json_decode($routes);
            //dd($result);
            foreach ($result as $dato) {
                $strName = $strName . "'" . $dato->FirstName . "',";
                $strDirName = $strDirName . "'" . $dato->AddressPatient . "',";
                if ($dato->durationmin > $maxTime) {
                    $maxTime = $dato->durationmin;
                    $wOrig = $dato->AddressPatient;
                }
            }
            $medicalc = Medical_centers::All();
            $xTimeB = date('g:i a', strtotime($xTimeB));
            $xTimeE = date('g:i a', strtotime($xTimeE));
            return view('gmaps.mapsroutes', ["medicalc" => $medicalc, "routes" => $strName, "dirpat" => $strDirName, "fecha" => $xDate, "horaini" => $xTimeB, "horaend" => $xTimeE, "origin" => $wOrig, "dest" => $xDest, "driver" => $idDriver]);
        } else {
            $msge = "There are no trips with the selected data";
            $medicalc = Medical_centers::All();
            $drivers = Driver_assigments::All();
            return view('gmaps.mapsrouteserror', ["statuse" => $msge]);
        }
    }


    //Raides
    function mapsarea()
    {
        $patients = ges_appoinments::where(['TripType' => 'A'])->get();
        $result = json_decode($patients);
        $drivers = Driver_assigments::where('Enable', '=', 1)
            /*->where('north','!=',null)->where('south','!=',null)
       ->where('east','!=',null)->where('west','!=',null)*/
            ->get();
        $drivers = json_decode($drivers);
        //$MiSQL="select d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor,count(a.FirstName) nPAtients FROM driver_assigments d INNER JOIN ges_appoinments a ON (d.Id=a.driver_id) WHERE a.TripType='A' and d.Enable=1 GROUP BY d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor ORDER BY d.Driver";
        $MiSQL = "select d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor,count(a.FirstName) nPAtients FROM driver_assigments d LEFT JOIN ges_appoinments a ON (d.Id=a.driver_id) GROUP BY d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor HAVING d.Enable=1 ORDER BY d.Driver";
        $xdrivers = DB::Select($MiSQL);

        $totald = 0;
        foreach ($xdrivers as $xdriver) {
            $totald = $totald + $xdriver->nPAtients;
        }

        $name = "";
        $dirname = "";
        $iddriver = "";
        $dsouth = "";
        $dnorth = "";
        $deast = "";
        $dwest = "";
        $lngorig = "";
        $lattitud = "";
        $colorpatient = "";

        $i = 0;
        //Patients
        foreach ($result as $dato) {
            if ($i < 500) {
                $name = $name . "'" . $dato->FirstName . "',";
                $dirname = $dirname . '"' . $dato->AddressPatient . '",';
                $lattitud = $lattitud . '"' . $dato->latorig . '",';
                $lngorig = $lngorig . '"' . $dato->lngorig . '",';
                $colorpatient = $colorpatient . '"' . $dato->drivercolor . '",';
            }
            $i++;
        }

        //Drivers
        $totalpatient = $patients->count();
        //$totald=$totalpatient-$totald;
        $drvname = $drvcolor = $drvnorth = $drvsouth = $drveast = $drvwest = '';
        foreach ($drivers as $driver) {
            if ($i < 500) {
                $drvname = $drvname . "'" . $driver->Driver . "',";
                $drvcolor = $drvcolor . "'" . $driver->pcolor . "',";
                $drvnorth = $drvnorth . $driver->north . ",";
                $drvsouth = $drvsouth . $driver->south . ",";
                $drveast = $drveast . $driver->east . ",";
                $drvwest = $drvwest . $driver->west . ",";
            }
            $i++;
        }

        $dnorth = 0;
        $dsouth = 0;
        $deast = 0;
        $dwest = 0;
        $dcolor = "#FFFFFF";
        $namedriver = "";
        return view('gmaps.mapsarea', [
            "strname" => $name,
            "dirname" => $dirname,
            "colorpatient" => $colorpatient,
            "drivers" => $xdrivers,
            "iddriver" => $iddriver,
            "drvsouth" => $drvsouth,
            "drvnorth" => $drvnorth,
            "drveast" => $drveast,
            "drvwest" => $drvwest,
            "drvcolor" => $drvcolor,
            "drvname" => $drvname,
            "namedriver" => $namedriver,
            "latorig" => $lattitud,
            "lngorig" => $lngorig,
            "patients" => $patients,
            "dcolor" => $dcolor,
            "showdiv" => "hidden",
            "totalpatient" => $totalpatient,
            "totald" => $totald,
        ]);
    }

    function getarea(Request $request)
    {
        $xidriver = "vacio";
        $xidd = "vacio";
        if (isset($request->idDriver)) {
            $xidriver = $request->idDriver;
        } else {
            $xidd = $request->xidd;
            $xfavcolor = $request->favcolor;
            $upddriver = Driver_assigments::where(['Id' => $xidd])->update(['south' => '25.82', 'north' => '25.87', 'east' => '-80.24', 'west' => '-80.30', 'pcolor' => $xfavcolor]);
            $xidriver = $xidd;
        }
        $patients = ges_appoinments::where(['TripType' => 'A'])->get();
        $result = json_decode($patients);
        /*var_dump($result);
  dd($patients);*/

        $MiSQL = "select d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor,count(a.FirstName) nPAtients FROM driver_assigments d LEFT JOIN ges_appoinments a ON (d.Id=a.driver_id) GROUP BY d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor HAVING d.Enable=1 ORDER BY d.Driver";
        $drivers = DB::Select($MiSQL);
        //$drivers=Driver_assigments::where(['Enable'=>1])->get();

        if ($xidriver > 0) {
            $driver = Driver_assigments::where(['Id' => $xidriver])->get();
            $showdiv = "";
        } else {
            $driver = Driver_assigments::where('Enable', '=', 1)
                /* ->where('north','!=',null)->where('south','!=',null)
       ->where('east','!=',null)->where('west','!=',null)*/
                ->get();
            $showdiv = "hidden";
        }
        //     dd($driver);
        $name = "";
        $dirname = "";
        $iddriver = "";
        $dsouth = "";
        $dnorth = "";
        $deast = "";
        $dwest = "";
        $lattitud = "";
        $lngorig = "";
        $colorpatient = "";
        $totald = "";
        $i = 0;
        foreach ($result as $dato) {
            if ($i < 500) {
                $name = $name . "'" . $dato->FirstName . "',";
                $dirname = $dirname . '"' . $dato->AddressPatient . '",';
                $lattitud = $lattitud . '"' . $dato->latorig . '",';
                $lngorig = $lngorig . '"' . $dato->lngorig . '",';
                $colorpatient = $colorpatient . '"' . $dato->drivercolor . '",';
            }
            $i++;
        }
        $totalpatient = $patients->count();
        $dsouth = $driver[0]->south;
        $dnorth = $driver[0]->north;
        $deast = $driver[0]->east;
        $dwest = $driver[0]->west;
        $dcolor = $driver[0]->pcolor;
        $iddriver = $xidriver;
        $namedriver = $driver[0]->Driver;
        $totald = 0;
        //Drivers
        $drvname = $drvcolor = $drvnorth = $drvsouth = $drveast = $drvwest = '';
        foreach ($driver as $xdriver) {
            if ($i < 500) {
                $totaldrv = ges_appoinments::where(['TripType' => 'A'])->where(['driver_id' => $xdriver->Id])->get();
                $totaldriver = $totaldrv->count();
                if (is_null($totaldriver)) {
                    $totaldriver = 0;
                }
                $drvname = $drvname . "'" . $xdriver->Driver . "',";
                $drvcolor = $drvcolor . "'" . $xdriver->pcolor . "',";
                $drvnorth = $drvnorth . $xdriver->north . ",";
                $drvsouth = $drvsouth . $xdriver->south . ",";
                $drveast = $drveast . $xdriver->east . ",";
                $drvwest = $drvwest . $xdriver->west . ",";
                $totald = $totald + $totaldriver;
            }
            $i++;
        }

        return view('gmaps.mapsarea', [
            "drivers" => $drivers,
            "strname" => $name,
            "dirname" => $dirname,
            "iddriver" => $iddriver,
            "dsouth" => $dsouth,
            "dnorth" => $dnorth,
            "deast" => $deast,
            "dwest" => $dwest,
            "dcolor" => $dcolor,
            "colorpatient" => $colorpatient,
            "namedriver" => $namedriver,
            "latorig" => $lattitud,
            "lngorig" => $lngorig,
            "patients" => $patients,
            "drvsouth" => $drvsouth,
            "drvnorth" => $drvnorth,
            "drveast" => $drveast,
            "drvwest" => $drvwest,
            "drvcolor" => $drvcolor,
            "drvname" => $drvname,
            "showdiv" => $showdiv,
            "totald" => $totald,
            "totalpatient" => $totalpatient,
        ]);
    }

    //Driver Assignment
    function getaread(Request $request)
    {
        $xidriver = "vacio";
        $xidd = "vacio";
        $xidriver = $request->idDriver;

        $MiSQL = "select d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor,count(a.FirstName) nPAtients FROM driver_assigments d INNER JOIN ges_appoinments a ON (d.Id=a.driver_id) WHERE a.TripType='A' and d.Enable=1 GROUP BY d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor ORDER BY d.Driver";
        $xdrivers = DB::Select($MiSQL);


        if (isset($request->idDriver)) {
            $selDriver = "";
            $item = array();
            foreach ($xidriver as $key => $driver) {
                array_push($item, $driver);
            }
        } else {
            $xidd = $request->xidd;
            $xfavcolor = $request->favcolor;
            $upddriver = Driver_assigments::where(['Id' => $xidd])->update(['south' => '25.82', 'north' => '25.87', 'east' => '-80.24', 'west' => '-80.30', 'pcolor' => $xfavcolor]);
            $xidriver = $xidd;
        }

        $drivers = Driver_assigments::where(['Enable' => 1])->get();

        if ($xidriver[0] > 0) {
            $selDriver = '7,8';
            $driver = Driver_assigments::whereIn('Id', $item)->get();
            //dd($driver);
            $showdiv = "";
            $patients = ges_appoinments::where(['TripType' => 'A'])->whereIn('driver_id', $item)->get();
        } else {
            $driver = Driver_assigments::where('Enable', '=', 1)
                //->where('north','!=',null)->where('south','!=',null)
                //->where('east','!=',null)->where('west','!=',null)
                ->get();
            $showdiv = "hidden";
            $patients = ges_appoinments::where(['TripType' => 'A'])->get();
        }
        $result = json_decode($patients);
        //     dd($driver);
        $name = "";
        $dirname = "";
        $iddriver = "";
        $dsouth = "";
        $dnorth = "";
        $deast = "";
        $dwest = "";
        $lattitud = "";
        $lngorig = "";
        $colorpatient = "";
        $totald = "";
        $i = 0;
        foreach ($result as $dato) {
            if ($i < 500) {
                $name = $name . "'" . $dato->FirstName . "',";
                $dirname = $dirname . '"' . $dato->AddressPatient . '",';
                $lattitud = $lattitud . '"' . $dato->latorig . '",';
                $lngorig = $lngorig . '"' . $dato->lngorig . '",';
                $colorpatient = $colorpatient . '"' . $dato->drivercolor . '",';
            }
            $i++;
        }

        $totalpatient = $patients->count();
        $dsouth = $driver[0]->south;
        $dnorth = $driver[0]->north;
        $deast = $driver[0]->east;
        $dwest = $driver[0]->west;
        $dcolor = $driver[0]->pcolor;
        $iddriver = $xidriver;
        $namedriver = $driver[0]->Driver;
        $totald = 0;
        //Drivers

        $drvname = array();
        $drvcolor = $drvnorth = $drvsouth = $drveast = $drvwest = '';
        foreach ($driver as $xdriver) {
            if ($i < 500) {
                $totaldrv = ges_appoinments::where(['TripType' => 'A'])->where(['driver_id' => $xdriver->Id])->get();
                $totaldriver = $totaldrv->count();
                //$drvname=$drvname."'".$xdriver->Driver."',";
                array_push($drvname, $xdriver->Driver);
                $drvcolor = $drvcolor . "'" . $xdriver->pcolor . "',";
                $drvnorth = $drvnorth . $xdriver->north . ",";
                $drvsouth = $drvsouth . $xdriver->south . ",";
                $drveast = $drveast . $xdriver->east . ",";
                $drvwest = $drvwest . $xdriver->west . ",";
                $totald = $totald + $totaldriver;
            }
            $i++;
        }

        return view('gmaps.mapsaread', [
            "drivers" => $xdrivers,
            "strname" => $name,
            "dirname" => $dirname,
            // "iddriver"=>$iddriver,
            "dsouth" => $dsouth,
            "dnorth" => $dnorth,
            "deast" => $deast,
            "dwest" => $dwest,
            "dcolor" => $dcolor,
            "colorpatient" => $colorpatient,
            "namedriver" => $namedriver,
            "latorig" => $lattitud,
            "lngorig" => $lngorig,
            "patients" => $patients,
            "drvsouth" => $drvsouth,
            "drvnorth" => $drvnorth,
            "drveast" => $drveast,
            "drvwest" => $drvwest,
            "drvcolor" => $drvcolor,
            "drvname" => $drvname,
            "showdiv" => $showdiv,
            "totald" => $totald,
            "totalpatient" => $totalpatient,
        ]);
    }

    function mapsaread()
    {
        $patients = ges_appoinments::where(['TripType' => 'A'])->get();
        $result = json_decode($patients);
        $drivers = Driver_assigments::where('Enable', '=', 1)
            // ->where('north','!=',null)->where('south','!=',null)
            // ->where('east','!=',null)->where('west','!=',null)
            ->get();
        $drivers = json_decode($drivers);
        $MiSQL = "select d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor,count(a.FirstName) nPAtients FROM driver_assigments d INNER JOIN ges_appoinments a ON (d.Id=a.driver_id) WHERE a.TripType='A' and d.Enable=1 GROUP BY d.Id,d.Driver,d.Enable,d.dZone,d.north,d.south,d.east,d.west,d.pcolor ORDER BY d.Driver";
        $xdrivers = DB::Select($MiSQL);

        $totald = 0;
        foreach ($xdrivers as $xdriver) {
            $totald = $totald + $xdriver->nPAtients;
        }

        $name = "";
        $dirname = "";
        $iddriver = "";
        $dsouth = "";
        $dnorth = "";
        $deast = "";
        $dwest = "";
        $lngorig = "";
        $lattitud = "";
        $colorpatient = "";

        $i = 0;
        //Patients
        foreach ($result as $dato) {
            if ($i < 500) {
                $name = $name . "'" . $dato->FirstName . "',";
                $dirname = $dirname . '"' . $dato->AddressPatient . '",';
                $lattitud = $lattitud . '"' . $dato->latorig . '",';
                $lngorig = $lngorig . '"' . $dato->lngorig . '",';
                $colorpatient = $colorpatient . '"' . $dato->drivercolor . '",';
            }
            $i++;
        }
        //dd($lattitud);
        //Drivers
        $totalpatient = $patients->count();
        //$totald=$totalpatient-$totald;
        $drvname = array();
        $drvcolor = $drvnorth = $drvsouth = $drveast = $drvwest = '';
        foreach ($drivers as $driver) {
            if ($i < 500) {
                //$drvname=$drvname."'".$driver->Driver."',";
                array_push($drvname, $xdriver->Driver);
                $drvcolor = $drvcolor . "'" . $driver->pcolor . "',";
                $drvnorth = $drvnorth . $driver->north . ",";
                $drvsouth = $drvsouth . $driver->south . ",";
                $drveast = $drveast . $driver->east . ",";
                $drvwest = $drvwest . $driver->west . ",";
            }
            $i++;
        }

        $dnorth = 0;
        $dsouth = 0;
        $deast = 0;
        $dwest = 0;
        $dcolor = "#FFFFFF";
        $namedriver = "";

        return view('gmaps.mapsaread', [
            "strname" => $name,
            "dirname" => $dirname,
            "colorpatient" => $colorpatient,
            "drivers" => $xdrivers,
            "iddriver" => $iddriver,
            "drvsouth" => $drvsouth,
            "drvnorth" => $drvnorth,
            "drveast" => $drveast,
            "drvwest" => $drvwest,
            "drvcolor" => $drvcolor,
            "drvname" => $drvname,
            "namedriver" => $namedriver,
            "latorig" => $lattitud,
            "lngorig" => $lngorig,
            "patients" => $patients,
            "dcolor" => $dcolor,
            "showdiv" => "hidden",
            "totalpatient" => $totalpatient,
            "totald" => $totald,
        ]);
    }

    ////

    function setarea(Request $request)
    {
        $xidriver = $request->aidd;
        $xeast = $request->zeast;
        $xwest = $request->awest;
        $xsouth = $request->asouth;
        $xnorth = $request->anorth;
        $upddriver = Driver_assigments::where(['Id' => $xidriver])->update(['south' => $xsouth, 'north' => $xnorth, 'east' => $xeast, 'west' => $xwest]);
        $patients = ges_appoinments::where(['TripType' => 'A'])->get();
        $result = json_decode($patients);
        $drivers = Driver_assigments::where(['Enable' => 1])->get();
        $driver = Driver_assigments::where(['Id' => $xidriver])->get();
        $totalpatient = $patients->count();
        $name    = "";
        $dirname = "";
        $iddriver = "";
        $dsouth  = "";
        $dnorth  = "";
        $deast   = "";
        $dwest   = "";
        $lattitud = "";
        $lngorig = "";
        $paso = "null";
        $colorpatient = "";
        $totald = "";
        $i = 0;
        foreach ($patients as $dato) {
            if ($dato->latorig <= $xnorth) $t1 = true;
            else $t1 = false;
            if ($dato->latorig >= $xsouth) $t2 = true;
            else $t2 = false;
            if ($dato->lngorig >= $xwest) $t3 = true;
            else $t3 = false;
            if ($dato->lngorig <= $xeast) $t4 = true;
            else $t4 = false;
            if (($t1) && ($t2) && ($t3) && ($t4))
                //$paso=$paso.' Id: '.$dato->id;
                ges_appoinments::where(['id' => $dato->id])
                    ->update(['Driver' => $driver[0]->Driver, 'driver_id' => $xidriver, 'drivercolor' => $driver[0]->pcolor]);
            // }
        }

        $patients = ges_appoinments::where(['TripType' => 'A'])->get();
        $result = json_decode($patients);

        foreach ($result as $dato) {
            if ($i < 500) {
                $name = $name . "'" . $dato->FirstName . "',";
                $dirname = $dirname . '"' . $dato->AddressPatient . '",';
                $lattitud = $lattitud . '"' . $dato->latorig . '",';
                $lngorig = $lngorig . '"' . $dato->lngorig . '",';
                $colorpatient = $colorpatient . '"' . $dato->drivercolor . '",';
            }
            $i++;
        }
        $dsouth = $driver[0]->south;
        $dnorth = $driver[0]->north;
        $deast = $driver[0]->east;
        $dwest = $driver[0]->west;
        $dcolor = $driver[0]->pcolor;
        $iddriver = $xidriver;
        $namedriver = $driver[0]->Driver;
        $totald = 0;
        //Drivers
        $totalpatient = $patients->count();
        $drvname = $drvcolor = $drvnorth = $drvsouth = $drveast = $drvwest = '';
        foreach ($driver as $xdriver) {
            if ($i < 500) {
                $totaldrv = ges_appoinments::where(['TripType' => 'A'])->where(['driver_id' => $xdriver->Id])->get();
                $totaldriver = $totaldrv->count();
                $drvname = $drvname . "'" . $xdriver->Driver . "',";
                $drvcolor = $drvcolor . "'" . $xdriver->pcolor . "',";
                $drvnorth = $drvnorth . $xdriver->north . ",";
                $drvsouth = $drvsouth . $xdriver->south . ",";
                $drveast = $drveast . $xdriver->east . ",";
                $drvwest = $drvwest . $xdriver->west . ",";
                $totald = $totald + $totaldriver;
            }
            $i++;
        }

        return view('gmaps.mapsarea', [
            "drivers" => $drivers,
            "strname" => $name,
            "dirname" => $dirname,
            "iddriver" => $iddriver,
            "dsouth" => $dsouth,
            "dnorth" => $dnorth,
            "deast" => $deast,
            "dwest" => $dwest,
            "dcolor" => $dcolor,
            "namedriver" => $namedriver,
            "latorig" => $lattitud,
            "lngorig" => $lngorig,
            "paso" => $paso,
            "patients" => $patients,
            "colorpatient" => $colorpatient,
            "drvsouth" => $drvsouth,
            "drvnorth" => $drvnorth,
            "drveast" => $drveast,
            "drvwest" => $drvwest,
            "drvcolor" => $drvcolor,
            "drvname" => $drvname,
            "showdiv" => "",
            "totald" => $totald,
            "totalpatient" => $totalpatient,
        ]);
    }

    function comparearea(Request $request)
    {
        $idd1 = $request->d1;
        $idd2 = $request->d2;
        $idd3 = $request->d3;

        $patients = ges_appoinments::where(['TripType' => 'A'])->get();
        $result = json_decode($patients);
        $drivers = Driver_assigments::where(['Enable' => 1])->get();
        $driver = Driver_assigments::where(['Id' => $idd1])->orwhere(['Id' => $idd2])->orwhere(['Id' => $idd3])->get();
        //dd($driver);
        $name    = "";
        $dirname = "";
        $iddriver = "";
        $dsouth  = "";
        $dnorth  = "";
        $deast   = "";
        $dwest   = "";
        $i = 0;
        foreach ($result as $dato) {
            if ($i < 1) {
                $name = $name . "'" . $dato->FirstName . "',";
                $dirname = $dirname . '"' . $dato->AddressPatient . '",';
            }
            $i++;
        }
        $dsouth1  = $driver[0]->south;
        $dnorth1  = $driver[0]->north;
        $deast1   = $driver[0]->east;
        $dwest1   = $driver[0]->west;
        $dcolor1  = $driver[0]->pcolor;
        $iddriver1 = $driver[0]->Id;
        $namedriver1 = $driver[0]->Driver;

        $dsouth2  = $driver[1]->south;
        $dnorth2  = $driver[1]->north;
        $deast2   = $driver[1]->east;
        $dwest2   = $driver[1]->west;
        $dcolor2  = $driver[1]->pcolor;
        $iddriver2 = $driver[1]->Id;
        $namedriver2 = $driver[1]->Driver;

        /*  dd([
        "drivers"=>$drivers,
        "strname"=>$name,
        "dirname"=>$dirname,
        "iddriver1"=>$iddriver1,
        "dsouth1"=>$dsouth1,
        "dnorth1"=>$dnorth1,
        "deast1"=>$deast1,
        "dwest1"=>$dwest1,
        "dcolor1"=>$dcolor1,
        "namedriver1"=>$namedriver1,
        "iddriver2"=>$iddriver2,
        "dsouth2"=>$dsouth2,
        "dnorth2"=>$dnorth2,
        "deast2"=>$deast2,
        "dwest2"=>$dwest2,
        "dcolor2"=>$dcolor2,
        "namedriver2"=>$namedriver2,
    ]);   */
        return view('gmaps.mapscomparea', [
            "drivers" => $drivers,
            "strname" => $name,
            "dirname" => $dirname,
            "iddriver1" => $iddriver1,
            "dsouth1" => $dsouth1,
            "dnorth1" => $dnorth1,
            "deast1" => $deast1,
            "dwest1" => $dwest1,
            "dcolor1" => $dcolor1,
            "namedriver1" => $namedriver1,
            "iddriver2" => $iddriver2,
            "dsouth2" => $dsouth2,
            "dnorth2" => $dnorth2,
            "deast2" => $deast2,
            "dwest2" => $dwest2,
            "dcolor2" => $dcolor2,
            "namedriver2" => $namedriver2,
        ]);
    }
    //********RIDES*********************
    public function showrides(Request $request)
    {
        //$xOrig=$request->wOrig;
        $xDest = "3850 SW 87th Ave, Miami, FL  33165";
        $xDate = "2022-03-21";
        $xTimeB = $request->wTimeB;
        $xTimeE = $request->wTimeE . ":00:01";
        $idDriver = 28;
        $strName = "";
        $strDirName = "";
        $wOrig = "";
        $maxTime = 0;

        $routes = DB::table('ges_appoinments')
            ->where('Date', '=', $xDate)
            /*->whereTime('Time', '>=', $xTimeB)
       ->whereTime('Time', '<=', $xTimeE)*/
            ->where('AddressDestination', '=', $xDest)
            ->where('Driver', '=', $idDriver)
            ->where('TripType', '=', 'A')
            ->get();

        $result = json_decode($routes);
        //dd($result);
        foreach ($result as $dato) {
            $strName = $strName . "'" . $dato->FirstName . "',";
            $strDirName = $strDirName . "'" . $dato->AddressPatient . "',";
            //$wOrig=$wOrig."'".$dato->AddressPatient."',";
            if ($dato->durationmin > $maxTime) {
                $maxTime = $dato->durationmin;
                $wOrig = $dato->AddressPatient;
            }
        }

        $medicalc = Medical_centers::All();
        return view('gmaps.maprides', ["medicalc" => $medicalc, "routes" => $strName, "dirpat" => $strDirName, "fecha" => $xDate, "horaini" => $xTimeB, "horaend" => $xTimeE, "origin" => $wOrig, "dest" => $xDest, "driver" => $idDriver]);
    }

    //MAPA RIDES
    function mapsarearide(Request $request)
    {
        $drivers = Driver_assigments::where(['enable' => 1])->get();
        $name = "";
        $dirname = "";
        $iddriver = "";
        $dsouth = "";
        $dnorth = "";
        $deast = "";
        $dwest = "";
        $lngorig = "";
        $lattitud = "";
        $i = 0;

        $triptype = $request->triptype;
        if ($triptype == "01") {
            $addorigen = $request->patientaddress;
            $adddest = $request->MedicalDest;
            $origin = "Patient address";
            $destination = "Medical Center";
        }
        if ($triptype == "02") {
            $addorigen = $request->MedicalDest;
            $adddest = $request->patientaddress;
            $origin = "Medical Center";
            $destination = "Patient address";
        }

        $MiSQL = 'select a.id,iddriver,u.Driver AS name,date_format(a.created_at,"%h:%i %p") fecha,a.latitud,a.longitud FROM ativity_tracks a INNER JOIN driver_assigments u ON (a.IdDriver=u.Id) WHERE a.id  in(select max(id) FROM ativity_tracks  group BY idDriver)';
        $routes = DB::Select($MiSQL);
        //dd($routes);
        //$result=json_decode($routes);
        foreach ($routes as $dato) {
            if ($i < 1000) {
                $name = $name . "'" . $dato->name . "->" . $dato->fecha . "',";
                $dirname = $dirname . '"' . $dato->name . '",';
                $lattitud = $lattitud . '"' . $dato->latitud . '",';
                $lngorig = $lngorig . '"' . $dato->longitud . '",';
                $xlat = $dato->latitud;
                $xlng = $dato->longitud;
            }
            $i++;
        }
        $dnorth = 25.87;
        $dsouth = 25.82;
        $deast = 80.24;
        $dwest = 80.30;
        $dcolor = "#FFFFFF";
        $namedriver = " DRIVER: All drivers";

        return view('gmaps.mapsarearide', [
            "drivers" => $drivers,
            "strname" => $name,
            "dirname" => $dirname,
            "iddriver" => 16,
            "dsouth" => $dsouth,
            "dnorth" => $dnorth,
            "deast" => $deast,
            "dwest" => $dwest,
            "dcolor" => $dcolor,
            "namedriver" => "TEST",
            "latorig" => $lattitud,
            "lngorig" => $lngorig,
            "xlat" => $xlat,
            "xlng" => $xlng,
            "namedriver" => " DRIVER: " . $namedriver,
            "addorigen" => $addorigen,
            "adddest" => $adddest,
            "origin" => $origin,
            "destination" => $destination
        ]);
    }

    function mapsrouteoneride(Request $request)
    {
        $drivers = Driver_assigments::where(['enable' => 1])->get();
        //dd($request);
        $name = "";
        $dirname = "";
        $iddriver = $request->iddriver;
        $dsouth = "";
        $dnorth = "";
        $deast = "";
        $dwest = "";
        $lngorig = "";
        $lattitud = "";
        $namedriver = "";
        $i = 0;

        if ($iddriver > 0) {
            //$MiSQL="select a.id,iddriver,u.name,a.created_at fecha,a.latitud,a.longitud FROM ativity_tracks a INNER JOIN users u ON (a.IdDriver=u.id) WHERE a.iddriver=".$iddriver;
            $MiSQL = "select a.id,iddriver,u.Driver AS name,a.created_at fecha,a.latitud,a.longitud FROM ativity_tracks a INNER JOIN driver_assigments u ON (a.IdDriver=u.Id) WHERE a.idDriver=" . $iddriver;
        } else {
            //$MiSQL=" select a.id,iddriver,u.name,a.created_at fecha,a.latitud,a.longitud FROM ativity_tracks a INNER JOIN users u ON (a.IdDriver=u.id) WHERE a.id  in(select max(id) FROM ativity_tracks  group BY iddriver)";
            $MiSQL = "select a.id,iddriver,u.Driver AS name,a.created_at fecha,a.latitud,a.longitud FROM ativity_tracks a INNER JOIN driver_assigments u ON (a.IdDriver=u.Id) WHERE a.id  in(select max(id) FROM ativity_tracks  group BY idDriver)";
        }

        $routes = DB::Select($MiSQL);
        //print_r($MiSQL);
        //dd($routes);
        //$result=json_decode($routes);
        foreach ($routes as $dato) {
            if ($i < 1000) {
                $name = $name . "'" . $dato->name . "->" . $dato->fecha . "',";
                $dirname = $dirname . '"' . $dato->name . '",';
                $lattitud = $lattitud . '"' . $dato->latitud . '",';
                $lngorig = $lngorig . '"' . $dato->longitud . '",';
                $xlat = $dato->latitud;
                $xlng = $dato->longitud;
            }
            $i++;
        }
        $dnorth = 25.87;
        $dsouth = 25.82;
        $deast = 80.24;
        $dwest = 80.30;
        $dcolor = "#FFFFFF";
        if ($iddriver > 0) {
            $namedriver = $dato->name;
        }
        //dd($lattitud);
        if ($iddriver > 0) {
            return view('gmaps.mapsrouteride', [
                "drivers" => $drivers,
                "strname" => $name,
                "dirname" => $dirname,
                "iddriver" => 16,
                "dsouth" => $dsouth,
                "dnorth" => $dnorth,
                "deast" => $deast,
                "dwest" => $dwest,
                "dcolor" => $dcolor,
                "namedriver" => "TEST",
                "latorig" => $lattitud,
                "lngorig" => $lngorig,
                "xlat" => $xlat,
                "xlng" => $xlng,
                "namedriver" => $namedriver,
            ]);
        } else {
            return view('gmaps.mapsarearide', [
                "drivers" => $drivers,
                "strname" => $name,
                "dirname" => $dirname,
                "iddriver" => 16,
                "dsouth" => $dsouth,
                "dnorth" => $dnorth,
                "deast" => $deast,
                "dwest" => $dwest,
                "dcolor" => $dcolor,
                "namedriver" => "TEST",
                "latorig" => $lattitud,
                "lngorig" => $lngorig,
                "xlat" => $xlat,
                "xlng" => $xlng,
                "namedriver" => $namedriver,
            ]);
        }
    }
    ///
}
