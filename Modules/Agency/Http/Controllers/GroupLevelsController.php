<?php

namespace Modules\Agency\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Agency\Models\GroupLevels;
use Modules\Agency\Transformers\GroupLevelsTransformer;
use Spatie\Fractal\Fractal;

class GroupLevelsController extends Controller
{

	public function __construct()
    {
        $this->middleware('auth:api');
    }
	
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $grouplevels = GroupLevels::get()->all();
        return fractal($grouplevels, new GroupLevelsTransformer());
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);
        GroupLevels::updateOrCreate([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return response()->json([ 'message' => 'Group added Successfully' ]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('agency::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('agency::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
