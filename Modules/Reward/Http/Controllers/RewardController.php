<?php

namespace Modules\Reward\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB;
use Illuminate\Foundation\Console\Presets\React;
use Helper;
use Modules\Reward\Models\QuantitySlot;
use Modules\Reward\Models\RewardDeliveryCharge;

class RewardController extends Controller
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

	public function QtySlotsList(Request $request){
        
		$finalArray = array();
		$GetQtySlotsList = QuantitySlot::orderBy('id','desc')->get(['id','name','min_value','max_value','delivery_charges']);
		if(!empty($GetQtySlotsList))
		{
			foreach ($GetQtySlotsList as $key => $value) { 
				$finalArray[$key]['id'] 				= Helper::customCrypt($value->id);
				$finalArray[$key]['name']				= $value->name;
				$finalArray[$key]['min_value']			= $value->min_value;
				$finalArray[$key]['max_value']			= $value->max_value;
				$finalArray[$key]['delivery_charges']	= $value->delivery_charges;
			}
		}
    
		return response()->json(['status' => true , 'data' => $finalArray], 200);
	
    }
	
	public function AddQtySlot(Request $request){
      
		try {
			
            $rules = [
                'name' 				=> 'required',
                'min_value'   		=> 'required|numeric',
                'max_value'   		=> 'required|numeric',
                'delivery_charges'	=> 'required|numeric',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['status' => false , 'message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

			$array = array();
			$array['name'] 				= $request['name'];
			$array['min_value'] 		= $request['min_value'];
			$array['max_value'] 		= $request['max_value'];
			$array['delivery_charges']	= $request['delivery_charges'];
			QuantitySlot::create($array);
			
			return response()->json(['status' => true , 'message' => 'Quantity Slot has been added successfully.'], 200);
            
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

	public function GetQtySlotByID(Request $request){
        
		try {
			
            $slotID = Helper::customDecrypt($request->slot_id);
            $request['slot_id'] = $slotID;

            $rules = [
                'slot_id' => 'required|exists:quantity_slots,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

			
			$finalArray = array();
			$GetQtySlotsList = QuantitySlot::where('id',$slotID)->get(['id','name','min_value','max_value','delivery_charges']);
			if(!empty($GetQtySlotsList))
			{
				foreach ($GetQtySlotsList as $key => $value) { 
					$finalArray[$key]['id'] 				= Helper::customCrypt($value->id);
					$finalArray[$key]['name']				= $value->name;
					$finalArray[$key]['min_value']			= $value->min_value;
					$finalArray[$key]['max_value']			= $value->max_value;
					$finalArray[$key]['delivery_charges']	= $value->delivery_charges;
				}
			}
			
            return response()->json(['status' => true , 'data' => $finalArray], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
	
    }

	public function UpdateQtySlotByID(Request $request){
      
		try {
			
            $slotID = Helper::customDecrypt($request->slot_id);
            $request['slot_id'] = $slotID;

            $rules = [
                'slot_id' 			=> 'required|exists:quantity_slots,id',
                'name' 				=> 'required',
                'min_value'   		=> 'required|numeric',
                'max_value'   		=> 'required|numeric',
                'delivery_charges'	=> 'required|numeric',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['status' => false , 'message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            if(!empty($slotID))
			{
				$array = array();
				$array['name'] 				= $request['name'];
				$array['min_value'] 		= $request['min_value'];
				$array['max_value'] 		= $request['max_value'];
				$array['delivery_charges']	= $request['delivery_charges'];
				QuantitySlot::where('id',$slotID)->update($array);
			}

            return response()->json(['status' => true , 'message' => 'Quantity Slot has been updated successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

	public function DeleteQtySlotByID(Request $request){
      
		try {
			
            $slotID = Helper::customDecrypt($request->slot_id);
            $request['slot_id'] = $slotID;

            $rules = [
                'slot_id' 	=> 'required|exists:quantity_slots,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['status' => false , 'message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            if(!empty($slotID))
			{
				QuantitySlot::where('id',$slotID)->delete();
			}

            return response()->json(['status' => true , 'message' => 'Quantity Slot has been deleted successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }
				
	public function DeliveryChargesList(Request $request){
        
		$finalArray = array();		
		$GetChargesList = RewardDeliveryCharge::join('product_catalogs', 'product_catalogs.id', '=', 'reward_delivery_charges.catalog_id','inner')
								->get(['reward_delivery_charges.id','reward_delivery_charges.catalog_id','product_catalogs.name as catalog_name']);
		if(!empty($GetChargesList))
		{
			foreach ($GetChargesList as $key => $value) 
			{	
				$finalArray[$key]['id'] 			= Helper::customCrypt($value->id);
				$finalArray[$key]['catalog_id'] 	= $value->catalog_id;
				$finalArray[$key]['catalog_name'] 	= $value->catalog_name;	
			}
		}
    
		return response()->json(['status' => true ,'data' => $finalArray], 200);
    }
	
	public function AddDeliveryCharges(Request $request){
      
		try {

            $rules = [
                'catalog_id' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['status' => false , 'message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

			$catalogIDs = $request['catalog_id'];
            //$catalogIDs = explode(',',$request['catalog_id']);

            $catalogIDs = explode(',',str_replace(array('[',']'),"",$request['catalog_id']));

			$catalog_IDs = array();
			$getCataLogIDs = RewardDeliveryCharge::select(DB::raw("group_concat(catalog_id) as catalog_IDs"))->first()->toArray();
			if(!empty($getCataLogIDs) && isset($getCataLogIDs['catalog_IDs']))
				$catalog_IDs = explode(",",$getCataLogIDs['catalog_IDs']);
				
			if(!empty($catalogIDs))
			{				
				foreach($catalogIDs as $key => $catalogID)
				{
					if(!in_array($catalogID,$catalog_IDs))
					{
						$array = array();
						$array['catalog_id'] = $catalogID;
						RewardDeliveryCharge::create($array);
					}
				}
			}
				
				return response()->json(['status' => true , 'message' => 'Delivery Charges has been added successfully.'], 200);
            
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

	public function GetDeliveryChargesByID(Request $request){
		return false;
		try {
			
            $slotID = Helper::customDecrypt($request->slot_id);
            $request['slot_id'] = $slotID;

            $rules = [
                'slot_id' => 'required|exists:quantity_slots,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

			$finalArray = array();			
			$GetChargesList = RewardDeliveryCharge::where('slot_id',$slotID)
										->join('quantity_slots', 'quantity_slots.id', '=', 'reward_delivery_charges.slot_id','inner')
										->join('product_catalogs', 'product_catalogs.id', '=', 'reward_delivery_charges.catalog_id','inner')
										->get(['reward_delivery_charges.id','quantity_slots.name','product_catalogs.name as catalog_name','quantity_slots.delivery_charges','reward_delivery_charges.catalog_id','reward_delivery_charges.slot_id']);
			if(!empty($GetChargesList))
			{
				foreach ($GetChargesList as $key => $value) {
					
					if($key == 0 )
					{
						$finalArray['slot_id'] 			= Helper::customCrypt($value->slot_id);
						$finalArray['name'] 			= $value->name;
						$finalArray['delivery_charges'] = $value->delivery_charges;	
					}
					
					$finalArray['catalogs'][$key]['catalog_id']		= $value->catalog_id;
					$finalArray['catalogs'][$key]['catalog_name']	= $value->catalog_name;
				}
			}
		
            return response()->json(['status' => true , 'data' => $finalArray], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }
	
	public function UpdateDeliveryChargesByID(Request $request){
      
		try {
		
            $rules = [
                'catalog_id' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['status' => false , 'message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $catalogIDs = explode(',',str_replace(array('[',']'),"",$request['catalog_id']));
            if(!empty($catalogIDs))
			{
				$deliveryIds = array();
				$getCataLogIDs = RewardDeliveryCharge::whereNotIn('catalog_id',$catalogIDs)->select(DB::raw("group_concat(id) as deliveryIds"))->first()->toArray();
				if(!empty($getCataLogIDs) && isset($getCataLogIDs['deliveryIds']))
					$deliveryIds = explode(",",$getCataLogIDs['deliveryIds']);
			
				if(!empty($deliveryIds))
				{
					RewardDeliveryCharge::whereIn('id',$deliveryIds)->delete();
				}
				
				foreach($catalogIDs as $key => $catalogID)
				{
					$array = array();					
					$check = RewardDeliveryCharge::where('catalog_id',$catalogID)->exists();
					if(empty($check))
					{
						$array['catalog_id'] 	= $catalogID;
						RewardDeliveryCharge::create($array);					
					}
				}
			}

            return response()->json(['status' => true , 'message' => 'Delivery Charges has been updated successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }
		
	public function DeleteDeliveryChargesByID(Request $request){
      
		return false;
		try {
			
            $slotID = Helper::customDecrypt($request->slot_id);
            $request['slot_id'] = $slotID;

            $rules = [
                'slot_id' 	=> 'required|exists:quantity_slots,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['status' => false , 'message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            if(!empty($slotID))
			{
				RewardDeliveryCharge::where('slot_id',$slotID)->delete();
			}

            return response()->json(['status' => true , 'message' => 'Delivery Charges has been deleted successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false , 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
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
