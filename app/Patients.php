<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patients extends Model
{
           
    protected $fillable = [
           'Id',
	         'MedicalNumber',
           'Names',
           'BOD',
           'NumberPhone1',
           'NumberPhone2',
           'Email',
           'ContactPreference',
           'PhysicalLimits',
           'IdMedicalC',
           'ContactPerson',
           'PreferredLanguage',
           'Route',
           'IdPatientType', 
           'PatientAddress',
           'Notes'
    ];

    public function patienttype()
    {
      return $this->belongsTo('App\Patient_type');
    } 

    public function gesappoinments()
    {
        return $this->hasMany('App\ges_appoinments','IdMC','Id');
    }

    public function address()
    {
        return $this->hasMany('App\PatientAddress','MedicalNumber','MedicalNumber');
    }

    public function scopePatient($query, $patient){

        if($patient)
                return $query->where('Names', 'LIKE', "%$patient%");
    }

  
}
