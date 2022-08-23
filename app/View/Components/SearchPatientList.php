<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SearchPatientList extends Component
{
    public $patients;
    
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($patients)
    {
        $this->patients = $patients;
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.search-patient-list');
    }
}
