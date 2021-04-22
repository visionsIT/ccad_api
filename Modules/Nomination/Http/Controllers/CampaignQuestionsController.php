<?php

namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use File;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Nomination\Exports\SubmissionCampaignRecordsExports;
use Modules\Nomination\Exports\NominationCampaignRecordsExports;
use Modules\Nomination\Exports\SendEcardsCampaignRecordsExports;
use Modules\Nomination\Exports\AnniversaryCampaignRecordsExports;
use Throwable;

class CampaignQuestionsController extends Controller
{
    private $repository;
    private $types_services;

    public function __construct()
    {
        $this->middleware('auth:api');
    }
	
	/***********Start Campaign Records Export************/
	
	public function SubmissionRecordsExport(Request $request)
    {
		$rules = [
			'campaignID' => 'required|integer|exists:value_sets,id',
		];

		$input = $request->all();
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
		}
		else
		{
			if(isset($input['campaignID']) && !empty($input['campaignID']))
			{
				$file = Carbon::now()->timestamp.'-SubmissionCampaignRecords.xlsx';				
				$path = public_path('uploaded/campaign/records/'.$file); 
				$responsePath = 'uploaded/campaign/records/'.$file;  
				Excel::store(new SubmissionCampaignRecordsExports($input), 'uploaded/campaign/records/'.$file, 'real_public');
				return response()->json([
					'file_path' => url($responsePath),
				]);

				//return Excel::download(new SubmissionCampaignRecordsExports($input), $file);
			}
			else
			{
				return response()->json(['message' => 'campaign ID is missing'], 422);
			}
		}
    }
	
	public function NominationRecordsExport(Request $request)
    {
		$rules = [
			'campaignID' => 'required|integer|exists:value_sets,id',
		];

		$input = $request->all();
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
		}
		else
		{
			if(isset($input['campaignID']) && !empty($input['campaignID']))
			{
				$file = Carbon::now()->timestamp.'-NominationCampaignRecords.xlsx';
				$path = public_path('uploaded/campaign/records/'.$file); 
				$responsePath = 'uploaded/campaign/records/'.$file;  
				Excel::store(new NominationCampaignRecordsExports($input), 'uploaded/campaign/records/'.$file, 'real_public');
				return response()->json([
					'file_path' => url($responsePath),
				]);

				//return Excel::download(new NominationCampaignRecordsExports($input), $file);
			}
			else
			{
				return response()->json(['message' => 'campaign ID is missing'], 422);
			}
		}
    }
		
	public function sendEcardRecordsExport(Request $request)
    {
		$rules = [
			'campaignID' => 'required|integer|exists:value_sets,id',
		];

		$input = $request->all();
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
		}
		else
		{
			if(isset($input['campaignID']) && !empty($input['campaignID']))
			{
				$file = Carbon::now()->timestamp.'-SendEcardCampaignRecords.xlsx';				
				$path = public_path('uploaded/campaign/records/'.$file); 
				$responsePath = 'uploaded/campaign/records/'.$file;  
				Excel::store(new SendEcardsCampaignRecordsExports($input), 'uploaded/campaign/records/'.$file, 'real_public');
				return response()->json([
					'file_path' => url($responsePath),
				]);

				//return Excel::download(new SendEcardsCampaignRecordsExports($input), $file);
			}
			else
			{
				return response()->json(['message' => 'campaign ID is missing'], 422);
			}
		}
    }	
	
	public function AnniversaryRecordsExport(Request $request)
    {
		$rules = [
			'campaignID' => 'required|integer|exists:value_sets,id',
		];

		$input = $request->all();
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
		}
		else
		{
			if(isset($input['campaignID']) && !empty($input['campaignID']))
			{
				$file = Carbon::now()->timestamp.'-AnniversaryCampaignRecords.xlsx';				
				$path = public_path('uploaded/campaign/records/'.$file); 
				$responsePath = 'uploaded/campaign/records/'.$file;  
				Excel::store(new AnniversaryCampaignRecordsExports($input), 'uploaded/campaign/records/'.$file, 'real_public');
				return response()->json([
					'file_path' => url($responsePath),
				]);

				//return Excel::download(new AnniversaryCampaignRecordsExports($input), $file);
			}
			else
			{
				return response()->json(['message' => 'campaign ID is missing'], 422);
			}
		}
    }
	
	/***********End Campaign Records Export************/
}
