<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\Product;

class ProductTransformer extends TransformerAbstract
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

        //echo "<pre>"; print_r($product->category->name); die;
        return [
            'id'           => $product->id,
            'name'         => $product->name,
            'value'        => $product->value,
            'image'        => $product->image,
            'quantity'     => $product->quantity,
            'likes'        => $product->likes,
            'model_number' => $product->model_number,
            'min_age'      => $product->min_age,
            'sku'          => $product->sku,
            'catalog_id'   => $product->catalog_id,
            'category_id'  => $product->category->id,
            'category_name'  => $product->category->name,
            'catalog_name' => $product->catalog->name,
            'type' => $product->type,
            'description' => $product->description,
            'terms_conditions' => $product->terms_conditions,
            'validity' => $product->validity,
            'base_price' => $product->base_price,
            'brand_id'      => $product->brand->id,
            'brand_name'    => $product->brand->name,
            'denominations' => $product->denominations()->select('id', 'value', 'points')->whereRaw('points >= ' . $minValue)->whereRaw('points <= ' . $maxValue)->orderBy('value','ASC')->get()->all(),
            'Sub'          => $product->sub()->select('id', 'name', 'value')->get()->all(),
            'seen'         => $product->product_seen ? $product->product_seen->account_id === 1 : FALSE,
            'status'  => $product->status,
        ];


    }

}
