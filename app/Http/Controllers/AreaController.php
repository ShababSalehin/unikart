<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Area;
use App\City;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->has('search')){
            $areas = Area::where('name','LIKE','%'.$request->search.'%')->paginate(15);
        }else{
        $areas = Area::paginate(15);
        }
        $citys = City::get();
        return view('backend.setup_configurations.area.index', compact('areas','citys'));
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
        $area = new Area;

        $area->name = $request->name;
        $area->citi_id = $request->citi_id;

        $area->save();

        flash(translate('Area has been inserted successfully'))->success();

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
         $area  = Area::findOrFail($id);
         $citys = City::get();
         return view('backend.setup_configurations.area.edit', compact('area','citys'));
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
        $area = Area::findOrFail($id);
            $area->name = $request->name;
        

        $area->citi_id = $request->citi_id;

        $area->save();


        flash(translate('Area has been updated successfully'))->success();
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
        $area = Area::findOrFail($id);

        Area::destroy($id);

        flash(translate('Area has been deleted successfully'))->success();
        return redirect()->route('area.index');
    }
}
