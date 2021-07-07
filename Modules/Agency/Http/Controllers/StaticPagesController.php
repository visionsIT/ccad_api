<?php

namespace Modules\Agency\Http\Controllers;

use Modules\Agency\Models\StaticPages;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Agency\Transformers\StaticPagesTransformer;

class StaticPagesController extends Controller
{
	public function __construct()
    {
        //$this->middleware('auth:api', ['except' =>['uploadImage','getImages']]);
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = StaticPages::all();
        if($pages) {
            return response()->json(['data'=>$pages, 'message'=>'Pages listed successfully.', 'status'=>'success']);
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'allies_name' => 'required|unique:static_pages,allies_name',
            'description' => 'required',
            'status' => 'boolean',
            'page_type' => 'required'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $description = [];
        $requestDescription = json_decode($request->description);
        $welcomeImageData = $requestDescription->welcome_image_data;
        $redeemSectionData = $requestDescription->redeem_section_data;
        

        if($request->hasFile('welcome_image')) {
            $file = $request->file('welcome_image');
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $imgName = $filename.time().'.'.$file_ext;
            $destinationPath = public_path('uploaded/ck_editor_images/');
            $file->move($destinationPath, $imgName);
            $welcomeImageData->url = url('/uploaded/ck_editor_images/'.$imgName);
        }
        foreach($redeemSectionData as $key => $value){

            if($request->hasFile('redeem_image')) {
                $file = $request->file('redeem_image');
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $imgName = $filename.time().'.'.$file_ext;
                $destinationPath = public_path('uploaded/ck_editor_images/');
                $file->move($destinationPath, $imgName);
                $redeemSectionData->image_url[] = url('/uploaded/ck_editor_images/'.$imgName);
            }
            $pageBlocksData = $value->page_blocks;
            if($request->hasFile('block_images')) {
                $files = $request->file('block_images');
                foreach ($files as $file) {
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $imgName = $filename.time().'.'.$file_ext;
                    $destinationPath = public_path('uploaded/ck_editor_images/');
                    $file->move($destinationPath, $imgName);
                    $exit = false;
                    
                    foreach($pageBlocksData as $key => $data) {
                        foreach($data->slides as $key2 => $slide) {
                            if(!$slide->block_image_url) {
                                $pageBlocksData[$key]->slides[$key2]->block_image_url = url('/uploaded/ck_editor_images/'.$imgName);
                                $pageBlocksData[$key]->slides[$key2]->blockfileName = '';
                                $pageBlocksData[$key]->slides[$key2]->blockImgDisplay = '';
                                $pageBlocksData[$key]->slides[$key2]->block_image = '';
                                $exit = true;
                                break;
                            }
                        }
                        if($exit) {
                            break;
                        }
                    }
                }
            }
        }

        
        $description = [
            'welcome_image_data' => $welcomeImageData,
            'redeem_section_data' => $redeemSectionData,
            'page_html' => $requestDescription->page_html
        ];

        $page = StaticPages::create([
            'title' => $request->title,
            'allies_name' => $request->allies_name,
            'page_type' => $request->page_type,
            'description' => json_encode($description),
            'status' => $request->status
        ]);

        if($page) {
            return response()->json(['message'=>'Page added successfully.', 'status'=>'success']);
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Modules\Agency\Models\StaticPages  $staticPages
     * @return \Illuminate\Http\Response
     */
    public function show(StaticPages $staticPages, $id)
    {
        $page = StaticPages::where('id', $id)->orWhere('allies_name', $id)->first();
        if($page) {

            //return fractal($page, new StaticPagesTransformer);

            return response()->json(['data'=>$page, 'message'=>'Page listed successfully.', 'status'=>'success']);
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Modules\Agency\Models\StaticPages  $staticPages
     * @return \Illuminate\Http\Response
     */
    public function edit(StaticPages $staticPages)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Agency\Models\StaticPages  $staticPages
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StaticPages $staticPages, $id)
    {

        $rules = [
            'title' => 'required',
            'allies_name' => 'required',
            'description' => 'required',
            'status' => 'required|boolean',
            'page_type' => 'required'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
            
        $page = StaticPages::find($id);

        if($page) {

            if(trim($page->allies_name) != trim($request->allies_name)) {

                $validator = \Validator::make($request->all(), ['allies_name' => 'unique:static_pages,allies_name']);
    
                if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            }

            $description = [];
            $requestDescription = json_decode($request->description);
            $welcomeImageData = $requestDescription->welcome_image_data;
            $redeemSectionData = $requestDescription->redeem_section_data;
            //$pageBlocksData = $requestDescription->page_blocks_data;

            if($request->hasFile('welcome_image')) {
                $file = $request->file('welcome_image');
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $imgName = $filename.time().'.'.$file_ext;
                $destinationPath = public_path('uploaded/ck_editor_images/');
                $file->move($destinationPath, $imgName);
                $welcomeImageData->url = url('/uploaded/ck_editor_images/'.$imgName);
            }

            foreach($redeemSectionData as $key => $value){
            
                if($request->hasFile('redeem_image')) {
                    $file = $request->file('redeem_image');
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $imgName = $filename.time().'.'.$file_ext;
                    $destinationPath = public_path('uploaded/ck_editor_images/');
                    $file->move($destinationPath, $imgName);
                    $redeemSectionData->image_url = url('/uploaded/ck_editor_images/'.$imgName);
                }
                $pageBlocksData = $value->page_blocks;
                if($request->hasFile('block_images')) {
                    $files = $request->file('block_images');
                    foreach ($files as $file) {
                        $file_name = $file->getClientOriginalName();
                        $file_ext = $file->getClientOriginalExtension();
                        $fileInfo = pathinfo($file_name);
                        $filename = $fileInfo['filename'];
                        $imgName = $filename.time().'.'.$file_ext;
                        $destinationPath = public_path('uploaded/ck_editor_images/');
                        $file->move($destinationPath, $imgName);
                        $exit = false;
                        foreach($pageBlocksData as $key => $data) {
                            foreach($data->slides as $key2 => $slide) {
                                if(!$slide->block_image_url) {
                                    $pageBlocksData[$key]->slides[$key2]->block_image_url = url('/uploaded/ck_editor_images/'.$imgName);
                                    $pageBlocksData[$key]->slides[$key2]->blockfileName = '';
                                    $pageBlocksData[$key]->slides[$key2]->blockImgDisplay = '';
                                    $pageBlocksData[$key]->slides[$key2]->block_image = '';
                                    $exit = true;
                                    break;
                                }
                            }
                            if($exit) {
                                break;
                            }
                        }
                    }
                }
            }


            $description = [
                'welcome_image_data' => $welcomeImageData,
                'redeem_section_data' => $redeemSectionData,
                //'page_blocks_data' => $pageBlocksData,
                'page_html' => $requestDescription->page_html
            ];
            $update_array =  array('title'=>$request->title,'allies_name'=>$request->allies_name,'page_type'=>$request->page_type,'description'=> json_encode($description),'status'=>$request->status);
            $update = StaticPages::where('id',$id)->update($update_array);

            if($update) {
                return response()->json(['message'=>'Page updated successfully.', 'status'=>'success']);
            } else {
                return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
            }

        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Modules\Agency\Models\StaticPages  $staticPages
     * @return \Illuminate\Http\Response
     */
    public function destroy(StaticPages $staticPages)
    {
        //
    }

    public function updatePageStatus(Request $request)
    {
        $rules = [
            'id'    => 'required|integer',
            'status'    => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        $page = StaticPages::find($request->id);

        if($page) {

            $page->status = $request->status;
            $page->save();

            return response()->json(['message'=>'Status changed successfully.', 'status'=>'success']);
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }

    }

    public function uploadImage(Request $request) 
    {
        $ruless = [
            'ckCsrfToken'    => 'required',
        ];
        $validatorr = \Validator::make($request->all(), $ruless);

        if ($validatorr->fails()) {
            return response()->json([
                'error'=>[
                    "message"=>"Invalid request.",
                    "number"=>203
                ]
            ]);
        }

        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            if($file_ext == 'jpg' || $file_ext == 'png' || $file_ext == 'jpeg') {
                // $rules = [
                //     'upload'    => 'image|max:5120',
                // ];
        
                // $validator = \Validator::make($request->upload, $rules);
        
                // if ($validator->fails()) {
                //     return response()->json([
                //         'error'=>[
                //             "message"=>"Invalid file. The file size is too big.",
                //             "number"=>203
                //         ]
                //     ]);
                // } else {
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $imgName = $filename.time().'.'.$file_ext;
                    $destinationPath = public_path('uploaded/ck_editor_images/');
                    $file->move($destinationPath, $imgName);
                    return response()->json([
                        "fileName"=>$imgName,
                        "uploaded"=>1,
                        "url"=>url('/uploaded/ck_editor_images/'.$imgName)
                    ]);
                // }
            } else {
                return response()->json([
                    'error'=>[
                        "message"=>"Only jpg, jpeg and png files allowed.",
                        "number"=>105
                    ]
                ]);
            }
        }
    }

    public function getImages(Request $request) 
    {   
        $directory = public_path('uploaded/ck_editor_images');
        $handle = opendir($directory);
        while($file = readdir($handle)){
            if($file !== '.' && $file !== '..'){
                $images[] = url('uploaded/ck_editor_images/'.$file);
            }
        }
        return response()->json(["files"=>$images]);
    }
}
