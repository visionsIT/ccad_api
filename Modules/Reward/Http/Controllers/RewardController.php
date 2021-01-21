<?php

namespace Modules\Reward\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB;
use Illuminate\Foundation\Console\Presets\React;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $reward_setting = DB::table('reward_settings')->first();
        return json_encode($reward_setting);
        exit;
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('reward::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'point_name' => 'required',
                'insufficient_point' => 'required',
                'gift_order_success' => 'required',
                'physical_order_success' => 'required',
                'choose_goal_item' => 'required',
                'view_rewards' => 'required',
                'view_vouchers' => 'required',
                'id' => 'required:integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
            $productData = [
                'point_name' => $request['point_name'],
                'insufficient_point' => $request['insufficient_point'],
                'gift_order_success' => $request['gift_order_success'],
                'physical_order_success' => $request['physical_order_success'],
                'choose_goal_item' => $request['choose_goal_item'],
                'view_rewards' => $request['view_rewards'],
                'voucher_display' => $request['view_vouchers'],
            ];
            DB::table('reward_settings')->where('id', $request->id)->update($productData);

            return response()->json(['message' => 'Reward settings has been updated successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('reward::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('reward::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }

    public function getCountries(){
        $countries = DB::table('countries')->where('status', '0')->get();
        return json_encode($countries);
        exit;
    }

    public function changeDeliveryStatus(Request $request)
    {
        try {
            $rules = [
                'change_delivery_status' => 'required',
                'id' => 'required:integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $productData = [
                'delivery_status' => $request['change_delivery_status'],
            ];
            DB::table('countries')->where('id', $request->id)->update($productData);

            return response()->json(['message' => 'Status has been updated successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    // public function ecardPermission(Request $request) {
    //     try {
    //         $input =  $request->all();

    //         DB::table('reward_settings')
    //           ->update(['ecards_display' => $input['display']]);
            
    //         return response()->json(['status' => true, 'message' => 'E-Cards display setting updated.']);
    //     } catch (\Throwable $th) {
    //         return response()->json(['status' => false, 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
    //     }
    // }
}
