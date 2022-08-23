<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Components\AppoinmentLogComponent;
use App\Components\TripsPlannerComponent;

class AppoinmentsController extends Controller
{
   
    public function tripsLog(Request $request){

        // si se agregan otros viajes es recomendable usar el metodo factory, ya que las clases usan la misma interfaz
        $date = $request->get('date');
        if (!isset($date))
            $date = date('Y-m-d');

        $today =date('Y-m-d');
        $pickDay = explode("-", $date);
        $today = explode("-", $today);

        if ($today[2] > $pickDay[2]){
            
            $workSheet = new AppoinmentLogComponent();
            $json = $workSheet->getSheetLog($workSheet->getData($request));
            return view(
                'logs.log_Sheet',
                $json
            );
            
          
        }else{
            $workSheet = new TripsPlannerComponent();
            $json = $workSheet->getTripsPlanner($workSheet->getData($request));
            return view(
                'tripsplanner.tripsplanner',
                $json
            );
        }
/*
        $workSheet = new AppoinmentLogComponent();
        $json = $workSheet->getSheetLog($workSheet->getData($request));
        return view(
            'logs.log_sheet',
            $json
        );
        */
    }
}
