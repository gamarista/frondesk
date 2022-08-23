<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Zones;
use App\Driver_assigments;

class ZoneController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
        ]);
    }

  
    public function create(){
        return view('admin.zones.create');
    }

    public function index()
    {
        $zones=Zones::paginate(15);
        return view('admin.zones.index',['zones'=>$zones]);
    }
    
    public function edit(Request $request)
    {

        $zone = Zones::where('IdZone', $request->id)->first();
        
        return view('admin.zones.edit',['zone'=>$zone]);
    }  

    public function update(Request $request)
    {
        Zones::where('IdZone', $request->id)->update(
            array(
                'Name' => $request->name,
                'description' => $request->description,

            ));
     
        return redirect()->route('resource.zone');

    }
    
    public function zoneRoute(Request $request){

        $routes = Driver_assigments::where('dZone', '=',  $request->id)->paginate(10);
      
        return view('admin.zones.zone-route',['routes'=> $routes ]);

    }
    
    public function store(Request $request){
        
        

            $zone = new Zones;
            $zone->Name = $request->name;
            $zone->description = $request->description;
            $zone->save();
            return redirect()->route('resource.zone');
       
    }
  


}
