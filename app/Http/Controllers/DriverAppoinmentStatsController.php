<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DriverAppoinmentStats;
use Illuminate\Support\Facades\Validator;

class DriverAppoinmentStatsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'end_date' => 'date|after_or_equal:start_date'
        ]);

        $startDate =  $request->get('start_date');
        $endDate =  $request->get('end_date');
        $center = $request->get('center');
        /*
            if (!empty( $startDate)){
                $startDate = \Carbon\Carbon::createFromFormat('Y-m-d h', $startDate . "0")->toDateTimeString();
            }
            if (!empty( $endDate)){
                $endDate = \Carbon\Carbon::createFromFormat('Y-m-d h', $endDate . "0")->toDateTimeString();
            }
        */
       
        $centers = DB::table('medical_centers')->pluck('Name', 'IdMedicalC');
     
        $activities = DriverAppoinmentStats::orderBy('start_job','ASC')
            ->center($center)
            ->date($startDate,$endDate)
            ->paginate(10);
        //dd($activities);
    
        return view(
            'logs.activity_log',
            [
                'centers' => $centers,      
                'activities' => $activities
            ]
        );
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
