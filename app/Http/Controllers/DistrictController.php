<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\Country;
use App\State;
use App\CityTranslation;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $search = $request->search;

        if(!empty($search)){

           $states = State::where('name', $request->search)->get();
        }else{
            $states = State::orderBy('id', 'desc')->where('status', 1)->get();
        }
        
        
        return view('backend.setup_configurations.district.index', compact('states'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $district = new State();
        $district->name = $request->name;
        $district->country_id = 18;
        $district->status = 1;
        $district->save();
        flash(translate('District has been inserted successfully'))->success();
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function edit(Request $request, $id)
     {
         $states = State::findOrFail($id);
         return view('backend.setup_configurations.district.edit', compact('states'));
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
        $district = State::findOrFail($id);
		$district->name = $request->name;
        $district->save();

        flash(translate('District has been updated successfully'))->success();
        return redirect()->route('district.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    
        flash(translate('District Should Not destroy'))->warning();
        return redirect()->route('district.index');
    }
    
   
}
