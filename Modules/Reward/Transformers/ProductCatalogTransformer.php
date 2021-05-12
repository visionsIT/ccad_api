<?php namespace Modules\Reward\Transformers;
use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\ProductCatalog;
use DB;
class ProductCatalogTransformer extends TransformerAbstract
{
    /**
     * @param Catalogue $ProductCatalog
     * @return array
     */
    public function transform(ProductCatalog $ProductCatalog): array
    {


        if (defined('COUNTRY_CODE')) {

            if(COUNTRY_CODE == ''){
                $country_id = '';
            }else{
                $country_id = COUNTRY_CODE;
            }
            
        
        }else{
            $country_id = '';
        }
        
        $subCateList = [];
        $subCat = $ProductCatalog->subCategories()->select('id', 'name', 'status')->get()->toArray();
        if(count($subCat) > 0){
            foreach ($subCat as $key => $value) {

                $subCateList[$key] = $value;

                if($country_id == ''){
                    $subCateList[$key]['product_count'] = DB::table('products')->select('*')->where('category_id', $value['id'])->where('status', '1')->count();
                }else{
                    $subCateList[$key]['product_count'] = DB::table('products')->leftJoin('products_countries', 'products_countries.product_id', '=', 'products.id')
                    ->where(function($query) use ($country_id){
                        $query->where('products_countries.country_id', $country_id)->orWhere('products.catalog_id',16);
                    })
                    ->where('products.category_id', $value['id'])
                    ->where('products.status', '1')
                    ->count();
                }
            }
        }

        if($country_id == ''){
            $count_final = DB::table('products')->select('*')->where('catalog_id', $ProductCatalog->id)->where('status', '1')->count();
        }else{
            $count_final = DB::table('products')->leftJoin('products_countries', 'products_countries.product_id', '=', 'products.id')
            ->where(function($query) use ($country_id){
                $query->where('products_countries.country_id', $country_id)->orWhere('products.catalog_id',16);
            })
            ->where('products.catalog_id', $ProductCatalog->id)
            ->where('products.status', '1')
            ->count();
        }
 
        return [
            'id'   => $ProductCatalog->id,
            'name' => $ProductCatalog->name,
            'status' => $ProductCatalog->status,
            //'product_count_old' => DB::table('products')->select('*')->where('catalog_id', $ProductCatalog->id)->where('status', '1')->count(),
            'product_count' => $count_final,
            //'sub_categories' => $ProductCatalog->subCategories()->select('id', 'name', 'status')->get()->all(),
            'sub_categories' => $subCateList,
        ];
    }
}