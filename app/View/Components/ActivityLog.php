<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ActivityLog extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $activities;
    public function __construct($activities)
    {
        $this->activities = $activities;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.activity-log');
    }
}
