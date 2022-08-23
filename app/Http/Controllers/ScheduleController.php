<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
  use App\Medical_centers;
   use App\Vehicles;
   use App\Driver_assigments;
   use App\Patients;
   use App\ges_appoinments;


class ScheduleController extends Controller
{
    
public function sregcalendars(Request $request)
    {
        $appoint=Appoinments::where(['MedicalNumber'=>$request->MedicalNumber,
            'IdMedicalC'=>$request->IdMedicalC,'AppoinmentDate'=>$request->AppoinmentDate])->get();
        if ($appoint->count()>0) {
            $data = ["status"=>0,"data"=>"The patient has an appointment for the selected date"];
        }
        else
        {
            $result = Appoinments::create($request->all());
            if($result)
                { $data = ["status"=>1,"data"=>$result];} else
                {$data = ["status"=>0,"data"=>"Error"];}
        }
        return response()->json($data, 200);
    }        

    public function getcalendar(Request $request)
    {
        $result = Appoinments::select('appoinments.IdRes','patients.Names','appoinments.PickUpTime','appoinments.DropOffTime')
                ->join('patients', 'appoinments.MedicalNumber', '=', 'patients.MedicalNumber')
                ->get();
            if($result)
                { $data = ["status"=>1,"data"=>$result];} else
                {$data = ["status"=>0,"data"=>"Error"];}
        return response()->json($data, 200);        
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


public function schedule(Request $request)
    {
    	/*if (!session()->has('apitoken')) 
    		{return view('userlogin',['status'=>'']);}*/
		//$roles=$this->GetAPI("getrol/".session('idrole'),"GET","");
		//$TAvailable=$this->GetAPI("getavailable","GET","");

        $TAvailable = ges_appoinments::groupBy('Date')->selectRaw('DATE_FORMAT(Date,"%Y-%m-%d") as PickUp, count(*) as total')->orderBy('Date','ASC')->Where('TripType','=','A')->get();
        $TotalSeats = Vehicles::groupBy('Enable')->selectRaw('Enable, sum(NumSeats) as total')->where('Enable', '=', 1)->get();
		$TotalS=json_decode($TotalSeats);
		$Available=json_decode($TAvailable);
	    $Total=$TotalS[0]->total;

		$i=0;
		$events="";
		foreach($Available as $dato)
		{
			$Avail=$Total-$dato->total;
			if ($Avail<$Total)
				{$xClassName='btn-success';}
			if ( ($Avail>=3) && ($Avail<=10))
				{$xClassName='btn-success';}			
			if ($Avail<=2)
				{$xClassName='btn-success';}			
			$m=date("m", strtotime($dato->PickUp))-1;
			$d=date("d", strtotime($dato->PickUp));
			//$events=$events."{title: 'Total:".$Total."', start: new Date(y, ".$m.", ".$d.",0), className: '".$xClassName."'},";
			$events=$events."{title: 'Appointments:".$dato->total."', start: new Date(y, ".$m.", ".$d.",1), className: '".$xClassName."'},";
			//$events=$events."{title: 'Available:".$Avail."', start: new Date(y, ".$m.", ".$d.",2), className: '".$xClassName."'},";
		}	
        $medicalc = Medical_centers::all()->sortBy("Name");
	    return view('schedule.schedule', ["medicalc"=>$medicalc,"events"=>$events]);    	
    }


    public function wschedule(Request $request)
    {

        $TAvailable = ges_appoinments::groupBy(['Date','Time'])->selectRaw('DATE_FORMAT(Date,"%Y-%m-%d") as PickUp, Time, count(*) as total')->orderBy('Time','ASC')->Where('TripType','=','A')->get();
        $TotalSeats = Vehicles::groupBy('Enable')->selectRaw('Enable, sum(NumSeats) as total')->where('Enable', '=', 1)->get();
		$TotalS=json_decode($TotalSeats);
		$Available=json_decode($TAvailable);
	    $Total=$TotalS[0]->total;

		$i=0;
		$events="";
		foreach($Available as $dato)
		{
			$Avail=$Total-$dato->total;
			$i=$i+$dato->total;
			if ($Avail<$Total)
				{$xClassName='btn-success';}
			if ( ($Avail>=3) && ($Avail<=10))
				{$xClassName='btn-warning';}			
			if ($Avail<=2)
				{$xClassName='btn-danger';}			

			$m=date("m", strtotime($dato->PickUp))-1;
			$d=date("d", strtotime($dato->PickUp));
			$y=date("Y", strtotime($dato->PickUp));

			$hpu=date("g", strtotime($dato->Time));
			$mpu=date("i", strtotime($dato->Time));

			$title="title: 'Available: ".$Avail." Appointments: ".$dato->total."',";
			$start="start: new Date(".$y.",".$m.",".$d.",".$hpu.",".$mpu."),";
			//$end="end: new Date(".$yd.", ".$md.", ".$dd.", ".$hdo.",".$mdo."),";
			$end="end: new Date(".$y.", ".$m.", ".$d.", ".$hpu.",".($mpu+55)."),";
			$allD="allDay: false,";
			$xClassName="className: '".$xClassName."'";
			//$events=$events."{".$title.$start.$end.$allD.$xClassName."},";
			$events=$events."{".$title.$start.$end.$allD.$xClassName."},";
		}        
	    return view('schedule.schedulew', ["events"=>$events,"totalapp"=>$i]);    	
    }


//Weekend
    public function wkschedule(Request $request)
    {
        $calendar = ges_appoinments::groupBy(['Date','Time'])->selectRaw('DATE_FORMAT(Date,"%Y-%m-%d") as PickUp, Time, count(*) as total')->orderBy('Time','ASC')->Where('TripType','=','A')->get();
        $TotalSeats = Vehicles::groupBy('Enable')->selectRaw('Enable, sum(NumSeats) as total')->where('Enable', '=', 1)->get();
		$TotalS=json_decode($TotalSeats);
	    $Total=$TotalS[0]->total;
		$wcalendar=json_decode($calendar);
//dd($wcalendar);
		$i=0;
		$events="";
		foreach($wcalendar as $dato)
		{
			$Avail=$Total-$dato->total;
			$i=$i+$dato->total;
			if ($Avail<$Total)
				{$xClassName='btn-success';}
			if ( ($Avail>=3) && ($Avail<=10))
				{$xClassName='btn-warning';}			
			if ($Avail<=2)
				{$xClassName='btn-danger';}				
			$m=date("m", strtotime($dato->PickUp))-1;
			$d=date("d", strtotime($dato->PickUp));
			$y=date("Y", strtotime($dato->PickUp));
			$hpu=date("g", strtotime($dato->Time));
			$mpu=date("i", strtotime($dato->Time));

			$title="title: 'Available seats:".$Avail."',";
			$start="start: new Date(".$y.",".$m.",".$d.",".$hpu.",".$mpu."),";
			
			$end="end: new Date(".$y.", ".$m.", ".$d.", ".$hpu.",".($mpu+59)."),";
			$allD="allDay: false,";
			$xClassName="className: '".$xClassName."'";
			$events=$events."{".$title.$start.$end.$allD.$xClassName."},";

		}	
	
	    return view('schedule.schedulewk', ["events"=>$events,"totalapp"=>$i]);    	
    }    


public function regcalendars(Request $request)
	{
		//$dato=$request->all();
		$dato=$request->except(['Name','NickName','NumberPhone','NumberPhone1','FaxNumber','Email','AddressMedicalC']);
		if ($request->filled('Name')) {
		  $datoCentro=$request->only(['Name','NickName','NumberPhone','NumberPhone1','FaxNumber','Email','AddressMedicalC']);    
		  $result=$this->GetAPI("regcenterm","POST",$datoCentro);
		  //dd($result);
		  $result=json_decode($result);
		  $dato["IdMedicalC"]=$result->data->id;
        }

		


		/*$date=date_create($request->PickUpTime);
		$date=$date->format('Y-m-d H:i:s');*/
		$miURL="getavailableday/".$request->PickUpTime;
	    $result=$this->GetAPI("sregcalendars","POST",$dato);
	    //dd($result);
	    $result=json_decode($result);
	    //dd($result);
	    $msg=$result->status;
	    if ($msg===1) 
	    	{	
	    		$result=$this->GetAPI($miURL,"GET","");
				$result=json_decode($result);
				$total=$result->data[0]->total;
	    		if ($total>=7)
	    		{$msg7="<<THERE ARE MORE THAN SEVEN PATIENTS REGISTRED AT THE SAME TIME>>";}
	    		else
	    		{$msg7="";}	
	    		$msg=Config::get('constants.resultapi.saved').$msg7;$msge='';}
	    if ($msg===0) 
	    	{$msg='';$msge=$result->data;} //Config::get('constants.resultapi.error')
	    if ($msg===2) 
	    	{$msg=Config::get('constants.resultapi.updated');$msge='';}
		return redirect()->route('calendar')->with(['status'=>$msg,'statuse'=>$msge]);

	}


}
