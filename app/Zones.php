<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zones extends Model
{
    protected $fillable = ['Name'];

    public function routes()
    {
    	return $this->hasMany('App\Driver_assigments','dZone','IdZone');
    }
}
