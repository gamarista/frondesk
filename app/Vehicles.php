<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicles extends Model
{
    protected $fillable = [
	'Model',
	'VehicleBrand',
	'VehicleReg',
	'NumSeats',
	'Enable',
    'Notes'
    ];

    public function driver()
    {
    	return $this->belongsTo('App\Driver_assigments','IdVehicle','IdVehicle');
    }       
}
