<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\traits\GesAppoinmentTrait;

class Appoinments extends Model
{
    protected $fillable = [
        'Id',
        'IdMC',
        'Time',
        'Date',
        'LastName',
        'FirstName',
        'MiddleName',
        'PatNumber',
        'DOB',
        'AddressPatient',
        'City',
        'State',
        'ZipCode',
        'PhoneNumber',
        'MobilNumber',
        'AddressDestination',
        'ConsultDestination',
        'TripType'

    ];

    public function patient()
    {
        return $this->belongsTo('App\Patients', 'IdMC', 'Id');
    }

    public function driver()
    {
        return $this->belongsTo('App\Driver_assigments', 'driver_id', 'Id');
    }
    public function scopeDriver($query, $driver)
    {
        if($driver)
            return $query->where('driver_id', '=', $driver);
    }
    public function scopeCenter($query, $center)
    {

        if($center)
            return $query->where('AddressDestination', 'LIKE', "%$center%");
    }
    public function scopeCenters($query, $center, $centers)
    {

        if ($center)
            return $query->where('AddressDestination', 'LIKE', "%$center%");
        else
            return $query->whereIn('AddressDestination', $centers->keys());
    }
    public function scopeDate($query, $date)
    {

        if ($date)
            return $query->where('Date', '=', $date);
    }
    public function scopePatient($query, $patient)
    {

        if($patient)
            return $query->whereRaw('concat(ges_appoinments.FirstName," ",ges_appoinments.LastName) like ?',["%$patient%"]);
    }
    public function scopeAppoinment($query, $appoinment, $centers)
    {
        if ($appoinment){
            if ($appoinment == 1){
                return $query
                        ->where('TripType','=','A')
                        ->whereIn('AddressDestination', $centers);
            }elseif($appoinment == 2){
                return $query
                        ->where('TripType','=','A')
                        ->whereNotIn('AddressDestination', $centers);
            }else{
                return $query->whereIn('TripType',['A']);
            }
        }
    }
    public function scopeTriptype($query, $tripType)
    {

        if ($tripType){

            if ($tripType == 'C')
                return $query->whereIn('TripType',['A','B']);
            else
                return $query->where('TripType','=',$tripType);
        }
    }

    public function scopeStatus($query, $status)
    {

        if ($status) {

            if ($status == '1') {
                return $query->whereNull('Driver');
            } elseif($status == '2'){

                return $query->whereNotNull('Driver');
            }
        }
    }

    public function scopeDestination($query, $destino){
        if($destino)
            return $query->where('AddressDestination', '=', $destino);
    }

    public function scopeTime($query, $xhora){
        if($xhora)
            return $query->whereTime('Time', '>=', $xhora)->whereTime('Time', '<=', $xhora.":00:01");

    } 

    public static function  getTrips($params,$sort,$centers){

        return  self::orderBy($sort, 'ASC')
        ->center($params['center'])
        ->driver($params['driver'])
        ->patient($params['patient_name'])
        ->appoinment($params['appoinment'], $centers->keys()->all())
        ->triptype($params['tripType'])
        ->status($params['status'])
        ->time($params['hora'],$params['horae'])
        ->destination($params['destino'])
        ->date($params['dateFilter'])
        ->paginate(10);
    }
}
