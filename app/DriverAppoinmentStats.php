<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverAppoinmentStats extends Model
{
    protected $fillable = [
        'id',
        'driver_id',
        'medical_centers_id',
        'start_job',
        'end_job',
        'total_trips',
        'completed_trips',
        'pending_trips',
        'ob',
        'dp',
        'cd'
    ];

    public function driver()
    {
        return $this->belongsTo('App\Driver_assigments', 'driver_id', 'Id');
    }
    public function center()
    {
        return $this->belongsTo('App\Medical_centers', 'medical_centers_id', 'IdMedicalC');
    }

    public function scopeCenter($query, $center){
        if($center)
          
            return $query->where('medical_centers_id', '=', $center);
    }
    public function scopeDate($query, $startDate,$endDate){

        if($startDate){
            if (strcmp($startDate,$endDate) == 0 ){
               
                return $query->whereRaw(
                    'SUBSTRING_INDEX(start_job," ",1) = ?',[$startDate]
                );
            }else{
               
                return $query->whereRaw(
                    'SUBSTRING_INDEX(start_job," ",1) >= ?',[$startDate]
                )->whereRaw(
                    'SUBSTRING_INDEX(end_job," ",1) <= ?',[$endDate]
                );
            }
        }
        
    }
}
