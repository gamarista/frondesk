<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CancelationCode extends Component
{

    public $cancellation;
   
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($cancellation)
    {
        $this->cancellation = $cancellation;
      
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.cancelation-code');
    }
}
