<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductsCountries;
use DB;
use Helper;
class ProductsTransformer extends TransformerAbstract
{


    /**
     * @param ue $product
     *
     * @return array
     */
    public function transform(Product $product): array
    {  
		$user_country_id = (isset($_GET['country_id']) && !empty($_GET['country_id'])) ? $_GET['country_id'] : false;
       
	   /* if ($handle = opendir( public_path().'/storage/products_img/')) {
            while (false !== ($fileName = readdir($handle))) {
                $newName = strtolower($fileName);

                if($newName != '.' && $newName != '..'){
                    rename(public_path().'/storage/products_img/'.$fileName, public_path().'/storage/products_img/'.$newName);
                }
            }
            closedir($handle);
        }
        die;*/
        if(!file_exists( public_path().'/storage/products_img/'.$product->image )){
            $product->image = 'defaultproductimage.png';
        }
        
        $minValue = 0;
        $maxValue = 99999999;

        if(isset($_SESSION['minValue'])){
            $minValue = $_SESSION['minValue'];
            $maxValue = $_SESSION['maxValue'];
        }

        if(!empty($product->category)){
            $category_id = $product->category->id;
            $category_name = $product->category->name;
        }else{ 
            $category_id = '';
            $category_name = '';
        }

        $country_data =  DB::table('products_countries')->select('countries.name', 'countries.id as country_id', 'countries.currency_code')->where(['products_countries.product_id' => $product->id])->join('countries', 'countries.id', '=', 'products_countries.country_id')->get();

        $productID = Helper::customCrypt($product->id);

        $user = \Auth::user();
        $user_data = $user_country = DB::table('program_users')->select('country_id')->where('account_id',$user->id)->first();

        $user_country_id = $user_data->country_id;

		if(!empty($user_country_id))
		{
			//$denomination = $product->denominations()->select('id', 'value', 'points')->whereRaw('points >= ' . $minValue)->whereRaw('points <= ' . $maxValue)->orderBy(DB::raw("points+0"), 'ASC')->get()->toArray();
            $product_country = DB::table('point_rate_settings')->where('country_id',$user_country_id)->first();
            
            

		}

        $login_currency = DB::table('countries')->select('id','currency_name as name','currency_code as code')->where('id',$user_country_id)->first();
		
        $denomination = $product->denominations()->select('id', 'value')->whereRaw('value >= ' . $minValue)->whereRaw('value <= ' . $maxValue)->orderBy(DB::raw("value+0"), 'ASC')->groupby('value')->get()->toArray();


        if(isset($product_country) && !empty($product_country)){
            $points = $product_country->points;
        }
        else{
            $points = '10';
        }

        $denomination_all = $denomination;
        foreach ($denomination_all as $key1 => $answer) {
            unset($denomination_all[$key1]['id']);
        }
        
        $denomination_final= array();
        foreach ($denomination as $key => $value) {

            
            
            $denomination_all[$key]['points']  = round(trim($value['value'])*$points,2);
            $denomination_all[$key]['id'] = Helper::customCrypt($value['id']);
            
        }


        return [
            'id'           => $productID,
            'name'         => $product->name,
            'value'        => $product->value,
            'image'        => $product->image,
            'quantity'     => $product->quantity,
            'likes'        => $product->likes,
            'model_number' => $product->model_number,
            'min_age'      => $product->min_age,
            'sku'          => $product->sku,
            'catalog_id'   => $product->catalog_id,
            'category_id'  => $category_id,
            'category_name'  => $category_name,
            'catalog_name' => $product->catalog->name,
            'type' => $product->type,
            'description' => $product->description,
            'terms_conditions' => $product->terms_conditions,
            'validity' => $product->validity,
            'base_price' => $product->base_price,
            'brand_id'      => $product->brand->id,
            'brand_name'    => $product->brand->name,
            'denominations' => $denomination_all,
            'Sub'          => $product->sub()->select('id', 'name', 'value')->get()->all(),
            'seen'         => $product->product_seen ? $product->product_seen->account_id === 1 : FALSE,
            'status'  => $product->status,
            //'country_id' => ProductsCountries::where('product_id',$product->id)->get(),
            'country_id' => $country_data,
            'currency_id' => $product->currency_id,
            'currency' => $login_currency,
            'conversion_rate' => Helper::customCrypt($points),
        ];


    }

}
