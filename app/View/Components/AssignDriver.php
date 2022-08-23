<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AssignDriver extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $drivers;
    public function __construct($drivers)
    {
        $this->drivers = $drivers;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.assign-driver');
    }
}
