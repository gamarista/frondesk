<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\traits\GesAppoinmentTrait;
use App\traits\TripsPlannerTrait;

class ges_appoinments extends Model
{

    use GesAppoinmentTrait;
    use TripsPlannerTrait;

    protected $fillable = [
        'Id',
        'IdOrder',
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
        'TripType',
        'ConfirmTrip',
        'driver_id',
        'Driver',
        'notified_driver',
        'dist',
        'distance_range',
        'distkm',
        'latorig',
        'lngorig',
        'latdest',
        'lngdest',
        'duration',
        'durationmin',
        'drivercolor',
        'statusGoogle',
    ];
    /*'Driver',
           'PhoneCompanion',
           'Companion'*/

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
        if ($driver)
            return $query->where('driver_id', '=', $driver);
    }

    public function scopeTime($query, $xhora, $ehora)
    {
        //dd($query);
        if ($xhora)
            //return $query->where('Time', '=', $xhora);
            $query->whereTime('Time', '>=', $xhora)->whereTime('Time', '<=', $ehora);
        //return $query->whereBetween('Time', [$xhora, $ehora]);
    }

    public function scopeDestination($query, $destino)
    {
        if ($destino)
            return $query->where('AddressDestination', '=', $destino);
    }

    public function scopeCenter($query, $center)
    {
        if ($center)

            return $query->where('IdMC', '=', (int)$center); //$query->where('AddressDestination', 'LIKE', "%$center%");
    }

    public function scopePatient($query, $patient)
    {

        if ($patient)
            return $query->whereRaw('concat(ges_appoinments.FirstName," ",ges_appoinments.LastName) like ?', ["%$patient%"]);
    }
    public function scopeAppoinment($query, $appoinment, $centers)
    {

        if ($appoinment) {
            if ($appoinment == 1) {
                return $query
                    ->where('TripType', '=', 'A')
                    ->whereIn('AddressDestination', $centers);
            } elseif ($appoinment == 2) {
                return $query
                    ->where('TripType', '=', 'A')
                    ->whereNotIn('AddressDestination', $centers);
            } else {
                return $query->whereIn('TripType', ['A']);
            }
        }
    }
    public function scopeTriptype($query, $tripType)
    {

        if ($tripType) {

            if ($tripType == 'C')
                return $query->whereIn('TripType', ['A', 'B']);
            else
                return $query->where('TripType', '=', $tripType);
        }
    }

    public function scopeNotConfirmTrip($query, $confirmTrip)
    {

        if ($confirmTrip == false)
            return $query->where('ConfirmTrip', '=', $confirmTrip);
    }

    public function scopeConfirmTrip($query, $confirmTrip)
    {

        if ($confirmTrip)
            return $query->where('ConfirmTrip', '=', $confirmTrip);
    }

    public function scopeStatus($query, $status)
    {

        if ($status) {

            if ($status == '1') {
                return $query->whereNull('Driver');
            } elseif ($status == '2') {

                return $query->whereNotNull('Driver');
            }
        }
    }

    public function scopeZone($query, $zone)
    {

        if ($zone)
            return $query->where('IdZone', '=', $zone);
    }

    public function scopeDay($query, $day)
    {

        if ($day)
            return $query->where('Date', '=', $day);
    }

    public static function  getTrips($params, $sort, $centers)
    {

        return  self::orderBy($sort, 'ASC')
            ->center($params['center'])
            ->driver($params['driver'])
            ->patient($params['patient_name'])
            ->appoinment($params['appoinment'], $centers->keys()->all())
            ->triptype($params['tripType'])
            ->status($params['status'])
            ->time($params['hora'], $params['horae'])
            ->destination($params['destino'])
            ->day($params['date'])
            ->paginate(10);
    }

    public static function  getTripsTypeBNotConfirmed($params, $sort, $centers)
    {
        //dd($params);
        $day = date('Y-m-d', strtotime('now'));

        return  self::orderBy($sort, 'ASC')
            ->center($params['center'])
            ->driver($params['driver'])
            ->patient($params['patient_name'])
            ->appoinment($params['appoinment'], $centers->keys())
            ->triptype($params['tripType']) //->triptype('B')
            ->notConfirmTrip($params['confirmTrip'])
            ->status($params['status'])
            ->time($params['hora'], $params['horae'])
            ->destination($params['destino'])
            ->day($day)    //$params['date']
            ->paginate(10);
    }

    public static function  getTripsTypeBConfirmed($params, $sort, $centers)
    {
        //dd($params);
        $day = date('Y-m-d', strtotime('now'));
        return  self::orderBy($sort, 'ASC')
            ->center($params['center'])
            ->driver($params['driver'])
            ->patient($params['patient_name'])
            ->appoinment($params['appoinment'], $centers->keys())
            ->triptype($params['tripType']) //->triptype('B')
            ->confirmTrip($params['confirmTrip'])
            ->status($params['status'])
            ->time($params['hora'], $params['horae'])
            ->destination($params['destino'])
            ->day($day)   //$params['date']
            ->paginate(10);
    }
}
