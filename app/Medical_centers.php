<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Medical_centers extends Model
{
    protected $fillable = [
    	'IdMedicalC', 
    	'Name',
        'NickName', 
    	'AddressMedicalC', 
    	'LatitudCenter',
    	'LongitudCenter',
    	'Specialty',
    	'NameDr',
    	'NumberPhone',
    	'NumberPhone1',
    	'FaxNumber',
    	'Email',
        'Street'
    	
    ];   
    public function appoinments()
    {
    	return $this->hasMany('App\Appoinments');
    }
    public function useradmin_center()
    {
        return $this->hasMany('App\UserAdmin_Center');
	}
	public function routes()
    {
        return $this->hasMany('App\Driver_assigments','IdMC','IdMedicalC');
    }              
}
