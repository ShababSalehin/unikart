<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\Country;
use App\State;
use App\CityTranslation;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search;
        
        if(!empty($search)) {
            $cities = City::where('name', 'like', "%$search%")->get();
        }else{
            $cities = City::orderBy('status', 'desc')->get();
        }
        //dd($cities);
        $states = State::where('status', 1)->get();
   

        return view('backend.setup_configurations.cities.index', compact('cities', 'states'));
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
        $city = new City;

        $city->name = $request->name;
        $city->cost = $request->cost;
        $city->state_id = $request->state_id;

        $city->save();

        flash(translate('City has been inserted successfully'))->success();

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
         $lang  = $request->lang;
         $city  = City::findOrFail($id);
         $countries = Country::where('status', 1)->get();
     	$states = State::where('status', 1)->get();
         return view('backend.setup_configurations.cities.edit', compact('city','states', 'lang', 'countries'));
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
        $city = City::findOrFail($id);
        // if($request->lang == env("DEFAULT_LANGUAGE")){
        //     $city->name = $request->name;
        // }
    
		$city->name = $request->name;
        $city->state_id = $request->state_id;
        $city->cost = $request->cost;

        $city->save();

        $city_translation = CityTranslation::firstOrNew(['lang' => $request->lang, 'city_id' => $city->id]);
        $city_translation->name = $request->name;
        $city_translation->save();

        flash(translate('City has been updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $city = City::findOrFail($id);

        foreach ($city->city_translations as $key => $city_translation) {
            $city_translation->delete();
        }

        City::destroy($id);

        flash(translate('City has been deleted successfully'))->success();
        return redirect()->route('cities.index');
    }
    
    public function get_city(Request $request) {
        $country_info = Country::where('status',1)->where('id', $request->country_id)->first();
        
        $cities = City::where('state_id', 1)->get();
        $html = '';
        
        foreach ($cities as $row) {
//            $val = $row->id . ' | ' . $row->name;
            $html .= '<option value="' . $row->name . '">' . $row->getTranslation('name') . '</option>';
        }
        
        
        echo json_encode($html);
    }
}
