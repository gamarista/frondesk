<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver_assigments extends Model
{
    protected $fillable = [
    	'Id', 
    	'dZone', 
    	'IdVehicle', 
    	'Driver',
    	'Phone1',
    	'Address',
    	'Notes',
		'Enable',
        'south',
        'north',
        'east',
        'west',
    ];
		
    public function zones()
    {
    	return $this->belongsTo('App\Zones','dZone','IdZone');
    }
    public function vehicule()
    {
    	return $this->hasOne('App\Vehicles','IdVehicle','IdVehicle');
	}    
	public function center()
    {
        return $this->belongsTo('App\Medical_centers','IdMC','IdMedicalC');
	}  
	public function user(){
		return $this->belongsTo('App\User');
	}   
	public function trips()
    {
        return $this->hasMany('App\ges_appoinments','driver_id','Id');
    }   

}
