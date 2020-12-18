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
        $subCateList = [];
        $subCat = $ProductCatalog->subCategories()->select('id', 'name', 'status')->get()->toArray();
        if(count($subCat) > 0){
            foreach ($subCat as $key => $value) {
                $subCateList[$key] = $value;
                $subCateList[$key]['product_count'] = DB::table('products')->select('*')->where('category_id', $value['id'])->where('status', '1')->count();
            }
        }
        return [
            'id'   => $ProductCatalog->id,
            'name' => $ProductCatalog->name,
            'status' => $ProductCatalog->status,
            'product_count' => DB::table('products')->select('*')->where('catalog_id', $ProductCatalog->id)->where('status', '1')->count(),
            //'sub_categories' => $ProductCatalog->subCategories()->select('id', 'name', 'status')->get()->all(),
            'sub_categories' => $subCateList,
        ];
    }
}