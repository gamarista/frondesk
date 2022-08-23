<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Patients;

class TripPlannerList extends Component
{
   public $appoinments;
   public $drivers;
   public $centers;
   public $requeriments;
   public $totalTrips;
   public $tripsAssigned;
   public $tripsNotAssigned;
   public $tripsCanceled;
   public $destinos;
   public $log;
   //public $totalFilter;
   //public $horas;
  
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($appoinments,$drivers, $centers, $requeriments , $totalTrips , $tripsAssigned,
                                $tripsNotAssigned,  $tripsCanceled, $destinos, $log) //, $totalFilter) //, $horas )
    {
        
        $this->appoinments = $appoinments ;
        $this->drivers = $drivers;
        $this->centers = $centers;
        $this->requeriments = $requeriments;
        $this->totalTrips = $totalTrips;
        $this->tripsAssigned = $tripsAssigned;
        $this->tripsNotAssigned = $tripsNotAssigned;
        $this->tripsCanceled = $tripsCanceled;
        $this->destinos = $destinos;
        $this->log = $log;
        //$this->totalFilter = $totalFilter;
        //$this->horas = $horas;
        //
    }
/*
    public function medicalNumber($IdMC){
        $medicalNumber = Patients::where('Id', $IdMC)->first();
        $mc = $medicalNumber['MedicalNumber'];
        //dd($medicalNumber->MedicalNumber);
        return $mc;
        //return "consigue trabajo vago";
    }*/

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.trip-planner-list');
    }
}
