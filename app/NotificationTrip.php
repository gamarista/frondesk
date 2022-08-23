<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationTrip extends Model
{
    protected $table = 'notification_trip';

    protected $fillable = [
        'id',
        'ges_appoinments_id',
        'driver_id',
        'message',
        'readed',
        'position',
        'created_at',
        'updated_at'
    ];

    public function appoinment()
    {
        return $this->belongsTo('App\ges_appoinments','ges_appoinments_id','id');
    }

    public function driver()
    {
        return $this->belongsTo('App\Driver_assigments','driver_id','id');
    }

}
