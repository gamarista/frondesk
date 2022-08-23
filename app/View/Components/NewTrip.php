<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NewTrip extends Component
{

    public $centers;
    public $patient;
    public $centers_info;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct( $centers, $patient,$centersInfo)
    {
       
        $this->centers = $centers->merge(['NC' => 'New center']);
        $this->patient = $patient;
        $this->centers_info = $centersInfo;
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.new-trip');
    }
}
