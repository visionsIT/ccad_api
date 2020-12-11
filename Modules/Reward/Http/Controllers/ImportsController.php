<?php

namespace Modules\Reward\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Reward\Imports\CategoryImport;
use Modules\Reward\Models\ProductBrand;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductCatalog;
use Modules\Reward\Models\ProductCategory;
use Modules\Reward\Models\ProductDenomination;
use Modules\Reward\Models\ProductOrder;
use Modules\Account\Models\Account;
use Spatie\Permission\Models\Role;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersGroupList;
use Modules\User\Models\UserRoles;
use Modules\User\Imports\UserImport;
use Modules\Reward\Imports\OrderImport;
use Modules\CommonSetting\Models\PointRateSettings;

class ImportsController extends Controller
{
    public function import(Request $request, $program_id)
    {
        //todo move uploaded file to a folder
        $file = $request->file('products');

        $uploaded = $file->move(public_path('uploaded/'.$program_id.'/products/csv/'), $file->getClientOriginalName());

        $products = Excel::toCollection(new CategoryImport(), $uploaded->getRealPath());


        $products = array_slice($products[0]->toArray(), 1);

        $validated_data = \Validator::make($products, [
          // "21.*" => 'required',
           //"22.*" => 'required',
           //"1.*" => "required|string",
           //"59.*" => "required|url",
        ]);

        if ($validated_data->fails()){
            return response()->json($validated_data->errors());
        }

        foreach ($products as $product){
            $catalog = ProductCatalog::updateOrCreate([
                'name' => $product[2],
            ]);

            if ($product[3] !== NULL){
                $category = ProductCategory::updateOrCreate([
                    'name' => $product[3],
                    'catalog' => $catalog->id
                ]);
            }

            Product::updateOrCreate([
                'name' => $product[0] ?? '',
                'image' => $product[1] ?? '',
                'sku' => $product[4] ?? '',
                'value' => $product[5] ?? '',
                'base_price' => NULL,
                'quantity' => 'available',
                'category_id' => $category->id ?? NULL,
                'catalog_id' => $catalog->id,
            ]);
        }

        return response()->json([
            'uploaded_file' => url('uploaded/'.$program_id.'/users/csv/'.$uploaded->getFilename()),
            'message' => 'Data Imported Successfully'
        ]);
    }

    public function importProductApi(Request $request) {
        try {
            $file = $request->file('product_file');
            $request->validate([
                'product_file' => 'required|file',
            ]);

            $uploaded = $file->move(public_path('uploaded/product_import_file/'), $file->getClientOriginalName());

            $products = Excel::toCollection(new CategoryImport(), $uploaded->getRealPath());

            $products = $products[0]->toArray();

            foreach ($products as $key => $product){
                if($key === 0) continue;
                if ($product[0] === '' || $product[0] === null) continue;

                $brand = ProductBrand::updateOrCreate([
                    'name' => $product[1],
                ]);

                $catalog = ProductCatalog::updateOrCreate([
                    'name' => $product[2],
                ]);

                $subCategory = ProductCategory::updateOrCreate([
                    'name' => $product[3],
                    'catalog' => $catalog->id,
                ]);

                $addedProduct = Product::updateOrCreate([
                    'sku' => ($product[0]!="")?$product[0]:'',
                    'name' => ($product[4]!="")?$product[4]:'',
                    'image' => ($product[10]!="")?strtolower($product[10]):'no-image',
                    'type' => $product[6],
                    'validity' => $product[7] ?? '',
                    'description' => ($product[8] != '')?$product[8]:'',
                    'terms_conditions' => ($product[9] != '')?$product[9]:'',
                    'quantity' => 'available',
                    'value' => 0,
                    'base_price' => 0,
                    'likes' => 0,
                    'model_number' => '',
                    'min_age' => '',
                    'catalog_id' => $catalog->id,
                    'category_id' => $subCategory->id,
                    'brand_id' => $brand->id,
                ]);

                $defaultCurrency = PointRateSettings::select('points')->where('default_currency','=','1')->first();
                if(empty($defaultCurrency)){
                    $getCurrencyPoints = '10';
                }else{
                    $getCurrencyPoints = $defaultCurrency->points;
                }

                if($product[5]!=""){
                    $denomi = explode(',', $product[5]);
                    foreach($denomi as $denoValue){
                        ProductDenomination::updateOrCreate([
                            'value' => $denoValue,
                            'points' => ((int)$denoValue*(int)$getCurrencyPoints),
                            'product_id' => $addedProduct->id,
                        ]);
                    }
                }
            } 
            return response()->json([
                'uploaded_file' => url('uploaded/product_import_file/'.$uploaded->getFilename()),
                'message' => 'Data Imported Successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }
    }

    /******************
    fn to import users
    ******************/
    public function importUserApi(Request $request){

        try {

            $file = $request->file('users_file');
            $request->validate([
                'program_id' => 'required|exists:programs,id',
                'users_file' => 'required|file',
            ]);
            $randm = rand(100,1000000);
            $fileNameSave = time() . "-users-" . $file->getClientOriginalName();
            $filename = $randm.'-'.$fileNameSave;

            $uploaded = $file->move(public_path('uploaded/user_import_file/'.$request->program_id.'/'), $filename);

            $users = Excel::toCollection(new UserImport(), $uploaded->getRealPath());
            $users = $users[0]->toArray();

            $rules = [
                '*.email' => "required|email|unique:program_users,email|unique:accounts,email",
                '*.username' => "required|unique:program_users,username",
                '*.communication_preference' => "in:email,sms",
                '*.group_name' => 'required|exists:roles,name',
                '*.role_name' => 'required|exists:user_roles,name',
            ];

            $validator = \Validator::make($users, $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            foreach ($users as $user){
                $password = Str::random(8);
                $account = Account::create([
                    'email' => $user['email'],
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'password' => $user['password'] ?? $password,
                    'contact_number' => $user['mobile'] ?? '',
                    'def_dept_id' => null,
                    'type' => 'user'
                ]);

                $email = $user['email'];
                $name = $user['first_name'] . ' ' . $user['last_name'];

                $this->sendPasswordCodeToAccount($email,$name,$password);

                $time = strtotime( $user['date_of_birth'] );

                $newformat = date('Y-m-d',$time);

                $programUser = ProgramUsers::create([
                    'first_name'                => $user['first_name'] ?? '',
                    'last_name'                 => $user['last_name'] ?? '',
                    'email'                     => $user['email'],
                    'username'                  => $user['username'],
                    'title'                     => $user['title'] ?? '',
                    'company'                   => $user['company'] ?? '',
                    'job_title'                 => $user['job_title'] ?? '',
                    'address_1'                 => $user['address_1'] ?? '',
                    'address_2'                 => $user['address_2'] ?? '',
                    'postcode'                  => $user['postcode'] ?? '',
                    'country'                   => $user['country'] ?? '',
                    'telephone'                 => $user['telephone'] ?? '',
                    'mobile'                    => $user['mobile'] ?? '',
                    'date_of_birth'             => $newformat   ,
                    'communication_preference'  => $user['communication_preference'] ?? 'email',
                    'language'                  => 'en',
                    'town'                      =>  '',
                    'account_id'                => $account->id,
                    'program_id'                => $request->program_id,
                    'emp_number'                => $user['emp_number'] ?? '',
                    'vp_emp_number'             => $user['vp_emp_number'] ?? '',
                ]);

                if($request->emp_type == 'lead'){
                    $programUser->vp_emp_number = $programUser->id;
                    $programUser->save();
                }

                #get_group_id
                $group_id = Role::select('id')->where('name', 'like', '%' . $user['group_name'] . '%')->first();
                $groupId = $group_id->id;

                #get_role_id
                $role_id = UserRoles::select('id')->where('name', 'like', '%' . $user['role_name'] . '%')->first();
                $roleId = $role_id->id;

                if ($groupId) {
                    $account->assignRole(Role::findById($groupId));
                }

                $date = date('Y-m-d h:i:s');

                $check_data = UsersGroupList::where(['account_id'=>$account->id,'user_group_id'=>$request->group_id,'user_role_id'=>$request->role_id])->first();
                if(empty($check_data)){
                    $UsersGroupList = new UsersGroupList;
                    $UsersGroupList->account_id = $account->id;
                    $UsersGroupList->user_group_id = $groupId;
                    $UsersGroupList->user_role_id = $roleId;
                    $UsersGroupList->created_at = $date;
                    $UsersGroupList->updated_at = $date;
                    $UsersGroupList->save();
                }
            }

            return response()->json([
                'uploaded_file' => url('uploaded/user_import_file/'.$request->program_id.'/'.$uploaded->getFilename()),
                'message' => 'Data Imported Successfully'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }
        
    }/******import user ends*****/

    public function sendPasswordCodeToAccount($email,$name,$password)
    {

        $image_url = [
            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
        ];

        $data = [
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ];

        try{
            Mail::send('emails.UserWelcomeMail', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
                $m->to($data["email"])->subject('Kafu by AD Ports - New Account');
            });
        }catch(\Throwable $th){
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }
        
    }

    /*******************
    fn to import orders
    *******************/
    public function importOrderApi(Request $request){
        try {
            $file = $request->file('order_file');
 
            $request->validate([
                'order_file' => 'required|file',
            ]);

            $randm = rand(100,1000000);
            $fileNameSave = time() . "-orders-" . $file->getClientOriginalName();
            $filename = $randm.'-'.$fileNameSave;

            $uploaded = $file->move(public_path('uploaded/order_import_file/'), $filename);

            $orders = Excel::toCollection(new OrderImport(), $uploaded->getRealPath());

            $orders = $orders[0]->toArray();
            $rules = [
                '*.email' => "required|email|exists:accounts,email",
                '*.category_name' => "required|exists:product_categories,name",
                '*.brand_name' => "required|exists:product_brands,name",
                '*.order_denomination' => "required|exists:product_denominations,value",
                '*.quantity' => "required",
            ];

            $validator = \Validator::make($orders, $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

           // echo "<pre>";print_r($orders);die;
            foreach ($orders as $key =>  $order){

                //if($key === 0) continue;

                if($order['email'] != ''){
                    $get_account = Account::where('email',$order['email'])->first();
                    if(!empty($get_account)){
                        $account_id = $get_account->id;
                    }
                }

                $where_condition = array();
                if($order['product_name'] != ''){
                    $where_condition['name'] = $order['product_name'];
                }
                if($order['category_name'] != ''){
                    $get_catid = ProductCategory::where('name',$order['category_name'])->first();
                    if(!empty($get_catid)){
                        $where_condition['category_id'] = $get_catid->id;
                    }
                }
                if($order['brand_name'] != ''){
                    $get_brandid = ProductBrand::where('name',$order['brand_name'])->first();
                    if(!empty($get_brandid)){
                        $where_condition['brand_id'] = $get_brandid->id;
                    }
                }
                $where_condition['status'] = '1';

                $get_product = Product::where($where_condition)->first();
                if(!empty($get_product)){
                    $product_id = $get_product->id;
                }else{
                    return response()->json(['status'=>'error','message'=>'Product not found']);
                }

                if($order['order_denomination'] != ''){
                    $check_denomination = ProductDenomination::where(['value'=>$order['order_denomination'],'product_id'=>$product_id])->first();
                    if(empty($check_denomination)){
                        return response()->json(['status'=>'error','message'=>'Denomination for this product is not found.']);
                    }
                }

                #get_total_price
                if($order['order_denomination'] != '' && $order['quantity'] != ''){
                    $total_value = $order['order_denomination'] * $order['quantity'];
                }

                ProductOrder::create([
                    'first_name'=> $order['first_name'] ?? '',
                    'last_name' => $order['last_name'] ?? '',
                    'email'     => $order['email'],
                    'phone'     => $order['phone'] ?? '',
                    'address'   => $order['address'] ?? '',
                    'city'      => $order['city'] ?? '',
                    'country'   => $order['country'] ?? '',
                    'is_gift'   => $order['is_gift'] ?? '',
                    'comment'   => $order['comment'] ?? '',
                    'value'     => $total_value ?? '',
                    'status'    => $order['status'] ?? '',
                    'product_id' => $product_id,
                    'account_id' => $account_id,
                ]);

            }

            return response()->json([
                'uploaded_file' => url('uploaded/order_import_file/'.$filename),
                'message' => 'Data Imported Successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }
    }/******import order ends*****/
}
