<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NotificationTrip;
use App\ges_appoinments;

class NotificationTripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  
        $gesAppoinments =ges_appoinments::find($request->ges_id);
        $message = new NotificationTrip;
        $message->message = $request->message;
        $message->ges_appoinments_id = $request->ges_id;
        $message->driver_id = $gesAppoinments->driver_id;

        if ($request->ajax()){

            $message->save();
            return response($message->toJson(), 200);
        }
      
       


        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($ges_appoinment_id)
    {
        
        $notifications = NotificationTrip::where('ges_appoinments_id', $ges_appoinment_id)->get();
        return view(
            'controlcenter.notifications',
            [
                'notifications' => $notifications,
                'appoinment' => $ges_appoinment_id
                
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    


}
