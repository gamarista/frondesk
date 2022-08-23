<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
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
    public function index()
    {
        return view('home');
    }

    public function test(Request $request)
    {
        // return view('rutadriver');

        return view('gmaps.index', ['origen' => $request->origen, 'destino' => $request->destino]);
    }

    public function reports(Request $request)
    {
        $xnamereport = $request->xnamereport;
        return view('reports', ['xnamereport' => $xnamereport]);
    }
}
