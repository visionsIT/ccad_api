<?php

namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\CampaignTypes;
use Modules\Nomination\Models\CampaignSettings;
use DB;
class ValueSetController extends Controller
{
    

    /**
     * API: Save Ripple setting as per the Admin input
     *
     * @return \Spatie\Fractal\Fractal
     */


    public function saveRippleSettings(Request $request)
    {
        try {

            $data = $request->all();
            $campaign_id = $data['campaign_id'];

            $rules = [
                'campaign_id' => 'required|integer|exists:value_sets,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

             ValueSet::where('id', $campaign_id)->update(['name' => $request->name,'status' => $request->status ]);


            // Check If campaign setting are there or not
            if (CampaignSettings::where('campaign_id', '=', $campaign_id)->count() > 0) {
                // Update
                $rules = [
                    'campaign_id' => 'required|integer|exists:campaign_settings,campaign_id',
                ];

                $validator = \Validator::make($request->all(), $rules);

                 if ($validator->fails())
                        return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                
                 CampaignSettings::where('campaign_id', $campaign_id)->update($request->all());


            }else{
                
                // Create
                $campain_setting = CampaignSettings::create([
                    'campaign_id' => $request->campaign_id,
                    'send_multiple_status' => $request->send_multiple_status,
                    'approval_request_status' => $request->approval_request_status,
                    'level_1_approval' => $request->level_1_approval,
                    'level_2_approval' => $request->level_2_approval,
                    'budget_type' => $request->budget_type,
                ]);
           
            }
            return response()->json(['message' => 'Settings has been updated successfully.'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        }
    }



}
