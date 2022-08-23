<?php
namespace App\Http\Controllers;
use Maatwebsite\Excel\Excel\Impoter;
use App\tmp_appoinments;
use App\Medical_centers;
use App\UserAdmin_Center;
use App\ges_appoinments;
use App\Imports\tmp_appoinmentImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class tmp_appoinmentsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $medicalcenters = Medical_centers::all();
        //$test = $medicalcenters->useradmin_center('IdMC');
        //dd($medicalcenters);
        return view('importexcel',['medicalcenters'=>$medicalcenters]);
    }

    public function indexkareo()
    {
        $medicalcenters = Medical_centers::all();
        //$test = $medicalcenters->useradmin_center('IdMC');
        //dd($medicalcenters);
        return view('importkareo',['medicalcenters'=>$medicalcenters]);
    }

    public function importcancel()
    {
        $medicalcenters = Medical_centers::all();
        ges_appoinments::truncate();
        return view('importexcel',['medicalcenters'=>$medicalcenters]);
    }

    public function importdataapi(Request $request)
     {
      $medicalcenters = Medical_centers::all();
      if (isset($request->dateStartImport) && isset($request->dateEndImport)) 
       { 
        tmp_appoinments::truncate();
           /* $data=tmp_appoinments::all();
            $numrec=$data->count(); 
            $data=tmp_appoinments::where(['Date'=>$request->dateImport])->get();
            $numrecerror=$data->count();*/
        $date=date_create($request->dateStartImport." ".$request->timeStartImport);
        $dateini=date_format($date,"m/d/Y G:i:s A");
        $date=date_create($request->dateEndImport." ".$request->timeEndImport);
        $datefin=date_format($date,"m/d/Y G:i:s A");   
        $IdMC=$request->IdMC;         
        ///IMPORTAR API
        $xml_data = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.kareo.com/api/schemas/">
           <soapenv:Header/>
           <soapenv:Body>
              <sch:GetAppointments>
                 <!--Optional:-->
                 <sch:request>
                    <sch:RequestHeader>
                       <!--Optional:-->
                       <sch:ClientVersion>1</sch:ClientVersion>
                       <!--Optional:-->
                       <sch:CustomerKey>e42ro57yt86f</sch:CustomerKey>
                       <!--Optional:-->
                       <sch:Password>Medgroup@305</sch:Password>
                       <!--Optional:-->
                       <sch:User>sacs@medgroupcenter.com</sch:User>
                       <sch:StartDate>$dateini</sch:StartDate>
                       <sch:EndDate>$datefin</sch:EndDate>
                    </sch:RequestHeader>
                    <sch:Fields>
                       <!--Optional:-->
                       <sch:AppointmentName></sch:AppointmentName>
                    </sch:Fields>
                    <!--Optional:-->
                    <sch:Filter>
                       <sch:Type>Patient</sch:Type>
                       <sch:ConfirmationStatus>Scheduled</sch:ConfirmationStatus>                      
                       <sch:StartDate>$dateini</sch:StartDate>
                       <sch:EndDate>$datefin</sch:EndDate>
                    </sch:Filter>
                 </sch:request>
              </sch:GetAppointments>
           </soapenv:Body>
        </soapenv:Envelope>
        XML;
//print_r($xml_data);
//dd($request);
        $url = "https://webservice.kareo.com/services/soap/2.1/KareoServices.svc";
        $page = "/KareoServices/GetAppointments";
        $headers = array(
            "POST ".$page." HTTP/1.0",
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"http://www.kareo.com/api/schemas/KareoServices/GetAppointments\"",
            "Content-length: ".strlen($xml_data)
            //"Authorization: Basic " . base64_encode($credentials)
        );
      
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_USERAGENT, strtolower($_SERVER['HTTP_USER_AGENT']));
       
        // Apply the XML to our curl call
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
        } else {

            file_put_contents("/home/ubuntu/test/sacswebdemo/storage/logs/dataapi.xml", $data);
            curl_close($ch);
            $this->importapi($IdMC);
        }

        $seletedmc = Medical_centers::where(['IdMedicalC'=>$request->IdMC])->get();
        $data=tmp_appoinments::All();
        file_put_contents("/home/ubuntu/test/sacswebdemo/storage/logs/dataapi.xml", "");
        $selectedmc=$seletedmc[0]->Name;
        $selecteddate=$request->dateImport;
        $numrec=$numrecerror=$data->count();
        if ($numrec<>$numrecerror)
            {
               $data=null;
               $msg=null;
               $selectedmc=null;
               $selecteddate=null;
               $xerror=$numrec-$numrecerror;
               $msge='There are '.$xerror.' rows imported with error on date, please check file';
               return view('importkareo', ['status'=>$msg,'statuse'=>$msge,'data'=>$data,'medicalcenters'=>$medicalcenters,'selectedmc'=>$selectedmc,'selecteddate'=>$selecteddate]);
            }
            $msge=null;
            $msg=$numrec.' rows were imported successfully.';
        }
        else {
            $data=null;
            $msg=null;
            $selectedmc=null;
            $selecteddate=null;            
            $msge='Select a medical center, a route date and file to import';
         }

            //$MiSQL = "update tmp_appoinments set IdMC=".$request->IdMC;
            //$drivers = DB::Select($MiSQL); 
         
         return view('importkareo', ['status'=>$msg,'statuse'=>$msge,'data'=>$data,'medicalcenters'=>$medicalcenters,'selectedmc'=>$selectedmc,'selecteddate'=>$selecteddate]);
        
       }

 
///DATA API KAREO
public function importapi($idmdc)
{
    $path = '/home/ubuntu/test/sacswebdemo/storage/logs/dataapi.xml';
    $dom = new \DomDocument();
    $dom->load($path) or die("error");
    $start = $dom->documentElement;
    $child = $start->childNodes;
    foreach($child as $item) {
        if($item->nodeType==XML_ELEMENT_NODE){
          $child2 = $item->childNodes;
          foreach($child2 as $item2) {
            $child3 = $item2->childNodes;
            foreach($child3 as $item3) {
             $child4 = $item3->childNodes;
             foreach($child4 as $item4) {
               $child5 = $item4->childNodes;
               foreach($child5 as $item5) {
                if ( $item5->nodeName == "AppointmentData") {
                  //print_r("   **CHILDS 5:     "); echo $item5->nodeName."<br>";
                  $child6 = $item5->childNodes;
                  foreach($child6 as $item7) {
                    if($item7->nodeType==XML_ELEMENT_NODE){
                      $nCampo=$item7->nodeName;
                      $nValor=htmlspecialchars_decode(trim($item7->nodeValue));
                      
                      switch ($nCampo) {
                        case 'StartDate':
                          $tmpDate=$nValor;
                          break;
                        case 'PatientFullName':
                          $tmpLastName=$nValor;
                          break;
                        case 'PatientID':
                          $tmpPatNumber=$nValor;
                          break;
                        case 'ServiceLocationName':
                          $tmpConsultDestination=$nValor;
                          break;
                        case 'Notes':
                          $tmpNotes=$nValor;
                          break;                          
                       }
                    }  
                  }
                  $tmp = new tmp_appoinments();
                  $tmp->IdMC=$idmdc;
                  $tmp->Date=$tmpDate;
                  $tmp->LastName=$tmpLastName;
                  $tmp->PatNumber=$tmpPatNumber;
                  $tmp->ConsultDestination=$tmpConsultDestination;
                  $tmp->Notes=$tmpNotes;
                  $tmp->save();
                 }
                }
               }
             }
            }
          } 
        }
      
}

//

    public function import(Request $request)
        {
           $medicalcenters = Medical_centers::all();
           if (isset($request->patients) && isset($request->dateImport) && $request->IdMC>0) 
           { 
            tmp_appoinments::truncate();
            $import = new tmp_appoinmentImport();
            //Excel::import($import, request()->file('alumnos'));
            //return $this->excel->import($import, 'test.xlsx');
            
            $reg = ($import)->import(request()->file('patients'),'local');

            /*\DB::table('tmp_appoinments')->whereId([1])->delete();
            \DB::table('tmp_appoinments')->whereId([2])->delete();
            \DB::table('tmp_appoinments')->whereId([3])->delete();
            \DB::table('tmp_appoinments')->whereId([4])->delete();*/
            //\DB::table('tmp_appoinments')->whereId([5])->delete();
            //\DB::table('tmp_appoinments')->whereId([6])->delete();

            $data=tmp_appoinments::all();
            $numrec=$data->count(); //print_r($numrec);
            //dd($data);
            $data=tmp_appoinments::where(['Date'=>$request->dateImport])->get();
            $numrecerror=$data->count();
            $seletedmc = Medical_centers::where(['IdMedicalC'=>$request->IdMC])->get();
            $selectedmc=$seletedmc[0]->Name;
            $selecteddate=$request->dateImport;
            if ($numrec<>$numrecerror)
            {
               $data=null;
               $msg=null;
               $selectedmc=null;
               $selecteddate=null;
               $xerror=$numrec-$numrecerror;
               $msge='There are '.$xerror.' rows imported with error on date, please check file';
               return view('importexcel', ['status'=>$msg,'statuse'=>$msge,'data'=>$data,'medicalcenters'=>$medicalcenters,'selectedmc'=>$selectedmc,'selecteddate'=>$selecteddate]);
            }
            $msge=null;
            $msg=$numrec.' rows in Excel file imported successfully.';}
           else {
            $data=null;
            $msg=null;
            $selectedmc=null;
            $selecteddate=null;            
            $msge='Select a medical center, a route date and file to import';}

            $MiSQL = "update tmp_appoinments set IdMC=".$request->IdMC;
            $drivers = DB::Select($MiSQL); 

            return view('importexcel', ['status'=>$msg,'statuse'=>$msge,'data'=>$data,'medicalcenters'=>$medicalcenters,'selectedmc'=>$selectedmc,'selecteddate'=>$selecteddate]);

        }

//*****************CALENDAR AVAILABLE***********************
    public function getavailable(Request $request)
    {
        $data = Appoinments::groupBy('PickUp')
        ->selectRaw('DATE_FORMAT(PickUpTime,"%Y-%m-%d") as PickUp, count(*) as total')->get();
        $data = ["status"=>1,"data"=>$data];
        return response()->json($data);             
    }

    public function getavailableday(Request $request)
    {
        $data = Appoinments::groupBy('PickUp')
        ->selectRaw('DATE_FORMAT(PickUpTime,"%Y-%m-%d") as PickUp, count(*) as total')
        ->where('PickUpTime', '=', $request->xday)
        ->get();
        //$data = ["status"=>1,"data"=>$data];
        $numRec=$data->count();
        if ($numRec>0)
          {$data = ["status"=>1,"data"=>$data,"numrec"=>$numRec];}
        else {
            $data=["total"=>0];
            $data = ["status"=>0,"data"=>$data];}  
        return response()->json($data);             
    }         
}
