<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAdmin_Center extends Model
{
    protected $fillable = [
	'IdDriver',
	'IdMC',
    ];
    public function users()
    {
    	return $this->belongsTo('App\users');
    } 
    public function medical_centers()
    {
    	return $this->belongsTo('App\Medical_centers');
    }     
}
