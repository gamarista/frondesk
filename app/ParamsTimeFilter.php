<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParamsTimeFilter extends Model
{
    protected $fillable = [
        'id',
        'value',
        'name'
    ];
}
