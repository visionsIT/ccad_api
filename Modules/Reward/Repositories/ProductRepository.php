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

    public function searchAdvance($keyword, $categoryId,$minValue,$maxValue, $subcategoryId, $productIds=null, $brandIds=0, $searchAd = '', $adminCall = '', $order = '', $col = '')
    {
        $query = $this->modeler->where("name", 'like', '%'.$keyword.'%');

        if ($categoryId > 0) {
            $query->where("catalog_id",'=', $categoryId);
        }

        if ($subcategoryId > 0) {
            $query->where("category_id",'=', $subcategoryId);
        }
        if($searchAd != ''){
            $query->whereIn('id', $productIds);
        }
        if($brandIds != ''){
            $query->whereIn('brand_id', $brandIds);
        }
        if($adminCall == ''){
            $query->where("status", "1");
        }
        if($order !='' && $col !=''){
            $query->orderBy($col,$order);
        } else {
            $query->orderByRaw("LENGTH(SUBSTRING_INDEX(value, '.', 1))", 'ASC')
            ->orderBy('value','ASC');
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
