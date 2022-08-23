<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ControlCenter extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $appoinments;
    public $drivers;
    public $centers;
    public $horas;


    public function __construct($appoinments,$drivers, $centers, $horas)
    {
        $this->appoinments = $appoinments ;
        $this->drivers = $drivers;
        $this->centers = $centers;
        $this->horas = $horas;
      
       
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.control-center');
    }
}
