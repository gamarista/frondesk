<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientAddress extends Model
{

    public function patient()
    {
        return $this->belongsTo('App\Patients','MedicalNumber','MedicalNumber');
    }
    //
}
