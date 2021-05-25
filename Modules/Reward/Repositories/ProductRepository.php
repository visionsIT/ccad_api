<?php namespace Modules\Reward\Repositories;

use App\Repositories\Repository;
use Modules\Reward\Models\Product;
use DB;


class ProductRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = Product::class;

    /**
     * @param $catalog_id
     *
     * @return mixed
     */
    public function sub_prod($catalog_id)
    {
        return Product::where("catalog_id", $catalog_id)
        ->orderByRaw("LENGTH(SUBSTRING_INDEX(value, '.', 1))", 'ASC')
        ->orderBy('value','ASC')->paginate(12);
        //->get();
    }

    /**
     * @param $keyword
     *
     * @return mixed
     */
    public function search($keyword)
    {
        return $this->modeler->where("name", 'like', "%$keyword%")->get();
    }

    public function searchAdvance($keyword, $categoryId,$minValue,$maxValue, $subcategoryId, $productIds=null, $brandIds=0, $searchAd = '', $adminCall = '', $order = '', $col = '', $country_id = '')
    {


        $query = $this->modeler->select('products.*', 'products_countries.id as p_countryId')
		->leftJoin('products_countries', 'products_countries.product_id', '=', 'products.id')
		->leftJoin('product_catalogs', 'product_catalogs.id', '=', 'products.catalog_id');
		
		$query->where("product_catalogs.status",'1'); 
        //$query = $this->modeler->where("name", 'like', '%'.$keyword.'%');
        $query->where("products.name", 'like', '%'.$keyword.'%');

        if($country_id){
            $query->where(function($q) use ($country_id){
                $q->where("products_countries.country_id", $country_id)->orWhere("products.catalog_id",16);
            });
        }
       
        if ($categoryId > 0) {
            $query->where("products.catalog_id",'=', $categoryId);
        }

        if ($subcategoryId > 0) {
            $query->where("products.category_id",'=', $subcategoryId);
        }
        if($searchAd != ''){
            $query->whereIn('products.id', $productIds);
        }
        if($brandIds != ''){
            $query->whereIn('products.brand_id', $brandIds);
        }
        if($adminCall == ''){
            $query->where("products.status", "1");
        }
        if($order !='' && $col !=''){
            $query->orderBy($col,$order);
        } else {
            $query->orderByRaw("LENGTH(SUBSTRING_INDEX(products.value, '.', 1))", 'ASC')
            ->orderBy('products.value','ASC');
        }
        $response = $query->paginate(12);
        return $response;
    }
    public function searchProductsByCategory($categoryId){
        if($categoryId == 0){
            $response = $this->modeler->orderBy('value','ASC')->get();
        } else {
            $response = $this->modeler->where("catalog_id",'=', $categoryId)->orderByRaw("LENGTH(SUBSTRING_INDEX(value, '.', 1))", 'ASC')->orderBy('value','ASC')->get();
        }
        return $response;
    }


}
