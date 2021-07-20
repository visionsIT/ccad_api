<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductsCountries;
use DB;
use Helper;
use Modules\Reward\Models\QuantitySlot;
use Modules\Reward\Models\RewardDeliveryCharge;
use Modules\User\Models\ProgramUsers;


class ProductsTransformer extends TransformerAbstract
{


    /**
     * @param ue $product
     *
     * @return array
     */
    public function transform(Product $product): array
    {  
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

        $product_country = DB::table('point_rate_settings')->where('default_currency','1')->first();

        if(empty($product_country)){
            $user = \Auth::user();
            $user_data = $user_country = DB::table('program_users')->select('country_id')->where('account_id',$user->id)->first();

            $user_country_id = $user_data->country_id;
            if(!empty($user_country_id))
            {
                //$denomination = $product->denominations()->select('id', 'value', 'points')->whereRaw('points >= ' . $minValue)->whereRaw('points <= ' . $maxValue)->orderBy(DB::raw("points+0"), 'ASC')->get()->toArray();
                $product_country = DB::table('point_rate_settings')->where('country_id',$user_country_id)->first();
                
                

            }
        }else{
            $user_country_id = $product_country->country_id;
        }


        
        //$login_currency = DB::table('countries')->select('id','currency_name as name','currency_code as code')->where('id',$user_country_id)->first();
		
        $denomination = $product->denominations()->select('id', 'value','price')->whereRaw('value >= ' . $minValue)->whereRaw('value <= ' . $maxValue)->orderBy(DB::raw("value+0"), 'ASC')->groupby('value')->get()->toArray();


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
        $user_data = \Auth::user();
        $user = ProgramUsers::where('account_id',$user_data->id)->with('currencyConversion')->first();
        
            foreach ($denomination as $key => $value) {
                if(is_numeric(trim($value['value'])) && is_numeric($points)){
                    if(isset($user->currencyConversion->conversion)){
                        $denomination_all[$key]['points']  = round(trim($value['price'])*$points,2)*$user->currencyConversion->conversion;
                    }
                    else{
                        $denomination_all[$key]['points']  = round(trim($value['price'])*$points,2);
                    }
                    $denomination_all[$key]['id'] = Helper::customCrypt($value['id']);
                }
            }
		$login_currency = array();
		$string = strtolower($product->catalog->name);
		if (strpos($string, 'international') !== false) {
			$login_currency = DB::table('countries')->select('id','currency_name as name','currency_code as code')->where("name", '=', $category_name)->first();
		}
		else
		{
			$login_currency = DB::table('countries')->select('id','currency_name as name','currency_code as code')->where('id',$user_country_id)->first();
		}

		/*
		$slots = array();
		if(!empty($product->catalog_id))
		{
			$slots = DB::table('reward_delivery_charges')
						->join('quantity_slots', 'quantity_slots.id', '=', 'reward_delivery_charges.slot_id','inner')
						->select('quantity_slots.id','quantity_slots.name','quantity_slots.min_value','quantity_slots.max_value','quantity_slots.delivery_charges')
						->where('reward_delivery_charges.catalog_id',$product->catalog_id)
						->get();
			if(!empty($slots))
			{
				foreach($slots as $key => $slot)
				{
					$slots[$key]->id = Helper::customCrypt($slot->id);
					$slots[$key]->delivery_charges_points = round(trim($slot->delivery_charges)*$points,2);
				}
			}
		}
		*/
		
		$slots = array();
		if(!empty($product->catalog_id))
		{
			$check = RewardDeliveryCharge::where('catalog_id',$product->catalog_id)->exists();
			if(!empty($check))
			{
				$slots = QuantitySlot::get(['quantity_slots.id','quantity_slots.name','quantity_slots.min_value','quantity_slots.max_value','quantity_slots.delivery_charges'])->toArray();
				if(!empty($slots))
				{
					foreach($slots as $key => $slot)
					{
						$slots[$key]['id'] = Helper::customCrypt($slot['id']);
						$slots[$key]['delivery_charges_points'] = round(trim($slot['delivery_charges'])*$points,2);
					}
				}
			}
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
			'slots' => $slots
        ];


    }

}
