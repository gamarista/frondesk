<?php

namespace App\Imports;

use App\tmp_appoinments;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
//use Maatwebsite\Excel\Concerns\{Importable, ToModel, WithHeadingRow, WithValidation};

class tmp_appoinmentImport implements ToModel //, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    use Importable;

    public function model(array $row)
    {
        //dd($row);
        return new tmp_appoinments([
           
          /* 'Time' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['time']),
           'Date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date']),
           'LastName' => $row['last_name'],
           'FirstName' => $row['first_name'],
           'MiddleName' => $row['middle_name'],
           'PatNumber'=> $row['pat_nbr'],
           'DOB'=> \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['dob']),
           'AddressPatient'=> $row['address'].', '.$row['city'].', '.$row['state'].', '.$row['zip'],
           'City'=> $row['city'],
           'State'=> $row['state'],
           'ZipCode'=> $row['zip'],
           'PhoneNumber'=> $row['phone_number'],
           'MobilNumber'=> $row['mobile_number'],
           'AddressDestination'=> $row['appt_svc_ctr_addr_line_1'],
           'ConsultDestination'=> $row['appt'], */
           //----------------
           //'TripType' => 'PU',
           /*'Driver'=> $row['Driver'],
           'PhoneCompanion'=> $row['PhoneCompanion'],
           'Companion'=> $row['Companion'],*/
//IMPORT FORMATO INICIAL
           /*'Time' => trim(substr( $row['11'], 0, (strlen($row['11']) - strpos($row['11'], '-')-3))),
           'Date' =>  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['14'])->format('Y-m-d'),
           'LastName' => '',
           'FirstName' => $row['0'], 
           'MiddleName' => '',
           'PatNumber'=> $row['1'],
           'DOB'=> '', //\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['3']),
           'AddressPatient'=> $row['4'], //.', '.$row['city'].', '.$row['state'].', '.$row['zip'],
           'City'=> '',
           'State'=> '',
           'ZipCode'=> 'Zip Code',
           'PhoneNumber'=> $row['2'],
           'MobilNumber'=> $row['3'],
           'AddressDestination'=> $row['6'],
           'ConsultDestination'=> $row['15'],  */
//IMPORT MEDGROUP
           'Time' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['4'])->format('H:m:s'),
           'Date' =>  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['8'])->format('Y-m-d'),
           'LastName' => '',
           'FirstName' => $row['0'], 
           'MiddleName' => '',
           'PatNumber'=> '',
           'DOB'=> '', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['8'])->format('Y-m-d'),
           'AddressPatient'=> $row['1'], //.', '.$row['city'].', '.$row['state'].', '.$row['zip'],
           'City'=> '',
           'State'=> '',
           'ZipCode'=> 'Zip Code',
           'PhoneNumber'=> $row['2'],
           'MobilNumber'=> $row['3'],
           'AddressDestination'=> $row['6'],
           'ConsultDestination'=> $row['6'],  
           'Driver'=> $row['7'],           
        ]);
    }
}
