<?php namespace Modules\Reward\Exports;


use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Reward\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use DB;

/**
 * Class UsersExport
 * @package Modules\User\Exports
 */
class RewardsExports implements FromCollection, WithHeadings
{
    protected $param;

    public function __construct($param = '')
    {
       $this->param = $param;
    }
    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|Product[]
     */
    public function collection()
    {
        if($this->param != ''){
            $search = $this->param['search'];
            $column = $this->param['column'];
            $order = $this->param['order'];
           // DB::enableQueryLog();
            $getRewardsList = DB::table('products')->select([
                    'products.id',
                    'products.name',
                    'image',
                    'sku',
                    'type',
                    'validity',
                    'description',
                    'terms_conditions',
                    'product_brands.name as brand_name',
                    'product_catalogs.name as category_name',
                    'product_categories.name as subcategory_name',
                    'products.status'
                ])->where('products.name', 'like', '%' . $search . '%')
                ->join('product_brands', 'products.brand_id', '=', 'product_brands.id')
                ->join('product_catalogs', 'products.catalog_id', '=', 'product_catalogs.id')
                ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
                ->orderBy('products.'.$column, $order)->get();

                //dd(DB::getQueryLog());
                //echo "<pre>"; print_r($getRewardsList); die;
                if(count($getRewardsList)>0){
                    foreach ($getRewardsList as $key => $value) {

                        $getCountries = DB::table('products_countries')->select('country_id','countries.name')->join('countries','countries.id','products_countries.country_id')->where('product_id', $value->id)->get();
                        $countries = '';
                        if(count($getCountries)>0){
                            foreach ($getCountries as $key_coun => $value_con) {
                                if($countries == ''){
                                    $countries = $value_con->name;
                                } else {
                                    $countries = $countries.', '.$value_con->name;
                                }
                            }
                        }
                        $getRewardsList[$key]->country = $countries;

                        #get_one_country_because_for_all_countries_same_denominations_Added
                        $getCountriesFirst = DB::table('products_countries')->select('country_id')->where('product_id', $value->id)->first();
                        //$getDenominations = DB::table('product_denominations')->select('value')->where(['product_id'=>$value->id,'country_id'=>$getCountriesFirst->country_id])->get();
                        $getDenominations = DB::table('product_denominations')->select('value','price')->where(['product_id'=>$value->id])->orderBy(DB::raw("value+0"), 'ASC')->groupby('value')->get();
                        $denominationList = '';
                        $priceList = '';
                        if(count($getDenominations)>0){
                            foreach ($getDenominations as $keyd => $valued) {
                                if($denominationList == ''){
                                    $denominationList = $valued->value;
                                } else {
                                    $denominationList = $denominationList.', '.$valued->value;
                                }
								
								if($priceList == ''){
                                    $priceList = $valued->value;
                                } else {
                                    $priceList = $priceList.', '.$valued->price;
                                }
                            }
                        }
                        $getRewardsList[$key]->denominations = $denominationList;                    
                        $getRewardsList[$key]->price = $priceList;                    
                    }
                }
            return $getRewardsList;
        }
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return ['#', 'Name', 'Image', 'SKU', 'Type', 'Validity', 'Description', 'Terms & Condition',  'Brand',  'Category',  'Sub Category', 'Status', 'Country', 'Denominations','Price'];
    }
}
