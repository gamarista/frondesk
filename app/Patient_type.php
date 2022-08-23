<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patient_type extends Model
{
    protected $fillable = ['IdType', 'Type'];
    public function patients()
    {
    	return $this->hasMany('App\Patients');
    }
}
