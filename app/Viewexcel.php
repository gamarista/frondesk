<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Viewexcel extends Model
{
    protected $fillable = [
		'Names',	
		'Dateofbirth',
		'NumberPhone1',
		'NumberPhone2',
		'PatientAddress',
		'Time',
		'AddressMedicalC',
		'Name',
		'SpecialistName',
		'NumberPhone',
		'TypeVisit',
		'SpecialTransportation',
		'MedicalNumber',
		'PickUpTime',
		'dropoff'
    ];    
    public function viewexcel()
    {
    }    
}
