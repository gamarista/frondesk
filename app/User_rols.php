<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_rols extends Model
{
    protected $fillable = [
	'Name',
	'Opc1',
	'Opc2',
	'Opc3',
	'Opc4',
	'Opc5',
	'Opc6',
	'Opc7',
	'Opc8',
	'Opc9'
    ];

    public function users()
    {
    	return $this->hasMany('App\User','IdRole','IdRole');
    }        
}
