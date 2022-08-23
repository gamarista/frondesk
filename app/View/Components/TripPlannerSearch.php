<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TripPlannerSearch extends Component
{

    public $centers;
    public $drivers;
    public $controlCenter;
    public $destinos;
    public $appoinments;
    public $log;
    public $date;
    public $horas;
    

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($centers, $drivers, $controlCenter, $destinos,$appoinments,$log,$date, $horas)
    {
        $this->centers = $centers;
        $this->drivers = $drivers;
        $this->controlCenter = $controlCenter; 
        $this->destinos = $destinos;
        $this->appoinments = $appoinments;
        $this->log = $log;
        $this->date = $date;
        $this->horas = $horas;

        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.trip-planner-search');
    }
}
