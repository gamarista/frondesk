<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\DB;

class ModalEditAppoinment extends Component
{

    public $appoinment;
    public $centers;
    public $addresses;
    public $requeriments;
    public $patienRequeriments;
    

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($appoinment, $centers, $requeriments)
    {
        $this->appoinment = $appoinment;
        $this->centers = $centers;
        $this->requeriments = $requeriments;
        $this->addresses  = DB::table('patient_addresses')->where('MedicalNumber',$this->appoinment->patient['MedicalNumber'])->pluck('Address','Address');
        $this->patienRequeriments = $this->getPatientRequeriments();
        //$this->addresses = $address;
        //
    }

    public function getPatientRequeriments(){
        /*
        $userRequeriment = array();
        foreach(json_decode($this->appoinment->special_requeriment,true) as $value){
           array_push($userRequeriment,intval($value));
         }
         return $userRequeriment;*/
         $collection = collect([]);
         if (isset($this->appoinment->special_requeriment)){
            $userRequeriment = json_decode($this->appoinment->special_requeriment,true);
            $cont = count($userRequeriment);
            for ($i = 0; $i < $cont ; $i++){
               $collection->push(intval($userRequeriment[$i]));
            }
         }
         return $collection;
    }
    public function showPickAnother(){
        $know = $this->showPickKnow();
        $center = $this->showPickCenter();

        if (!isset($know) && !isset($center) ) {
            return $this->appoinment->AddressPatient;

        }else{
          return null;
        }
    }

    public function showPickKnow(){
        
        foreach(  $this->addresses  as $address){
           
            if (strcmp($this->appoinment->AddressPatient, $address) == 0){
                return $address;
            }
        }

        return null;
        
    }
    public function showPickCenter(){
        foreach(  $this->centers as $key => $vale){
            if ( strcmp($this->appoinment->AddressPatient,$key) == 0)
              return $key;
        }
        return null;
    }

    public function showDropoffAnother(){
        $know = $this->showDropoffPickKnow();
        $center = $this->showDropoffCenter();
       

        if (!isset($know) && !isset($center) ) {
            return $this->appoinment->AddressDestination;

        }else{
          return null;
        }
    }

    public function showDropoffPickKnow(){
      
        foreach(  $this->addresses  as $address){
           
            if (strcmp($this->appoinment->AddressDestination, $address) == 0){
                return $address;
            }
        }

        return null;
    }
    public function showDropoffCenter(){
        foreach(  $this->centers as $key => $vale){
            if ( strcmp($this->appoinment->AddressDestination,$key) == 0)
              return $key;
        }
        return null;
    }

    public function showDestinationType(){
        $type = $this->showDropoffCenter();
        if (isset($type)){
            return "Inside";
        }else{
            return "Outside";

        }

    }
    
    public function getAddress(){
        // PRUEBA FALLIDA, NO SE POR QUE NO AGARRA SI TRAE LOS DATOS IGUAL A UNA COLECCION DE ELOQUENT CON PLUCK
        if ($this->appoinment->patient['address'] == null){
            return "vacio";
        }
   
        $adresses = json_encode($this->appoinment->patient['address'], true);
        $adresses = ltrim($adresses,'[');
        $adresses = rtrim($adresses,']');
        $pos = 0;
        $cont = 0;
        $ban = true;
        while($ban == true){
            $pos = strpos( $adresses , "MedicalNumber", $pos+1);
            if (is_numeric($pos)){
                $cont++;
            }else{
                $ban = false;
            }
        }

        $patient_address = collect();
        $collection = collect(['Address','Address']);
        for($i = 0; $i <  $cont ; $i++){
            $collection = collect([$this->appoinment->patient['address'][$i]['Address'],$this->appoinment->patient['address'][$i]['Address']]);
            $combined = $collection->combine([$this->appoinment->patient['address'][$i]['Address'],$this->appoinment->patient['address'][$i]['Address'] ]);
            return $combined->all();
            $patient_address->push($combined);  
            //$combined->all(); 
            //return $combined->toJson();
        }
        return $patient_address->toJson();
      
    }
    
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.modal-edit-appoinment');
    }
}
