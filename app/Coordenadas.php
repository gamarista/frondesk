<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coordenadas extends Model
{
    protected $fillable = [
	'dirhash',
	'address',
	'longitud',
	'latitud',
    ];
}
