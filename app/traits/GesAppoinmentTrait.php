<?php
namespace App\traits;

trait GesAppoinmentTrait
{

    /**
     * @param $tags string or array
     */
    public function attetionType($attetion)
    {
       switch($attetion){

           case 1: return "PCP";
           case 2: return "Wellness";
           case 3: return "Specialist";
           default: null;
       
       }
        // Code here
    }

    public function serviceType($service){

        switch($service){

            case 1: return "A";
            case 2: return "B";
            case 3: return "A";
            default: null;
        
        }

    }


}