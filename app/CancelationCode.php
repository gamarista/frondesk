<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CancelationCode extends Model
{
    protected $table = 'cancellation_code';

    
    protected $fillable = [
        'CANCELLATION_CODE',
        'CANCELATTION_TEXT'
    ];
    //
}
