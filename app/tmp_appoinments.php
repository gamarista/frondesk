<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tmp_appoinments extends Model
{
    protected $fillable = [
          /* 'Id',
           'IdMC',*/
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
           'TriType',
           'Driver',
           /*'PhoneCompanion',
           'Companion'*/
    ];
}

