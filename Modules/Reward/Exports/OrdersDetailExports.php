<?php namespace Modules\Reward\Exports;

use Modules\Reward\Models\ProductOrder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use Carbon\Carbon;
use Helper;

/**
 * Class OrdersDetailExports
 * @package Modules\Reward\Exports
 */
class OrdersDetailExports implements WithHeadings, WithMapping ,FromCollection
{
	protected $param;

    public function __construct($param = '')
    {
       $this->param = $param;
    }
	
	public function map($records) :array
    {		
		try{
			$first_name = (isset($records->first_name)) ? ucfirst($records->first_name) : '';
			$last_name 	= (isset($records->last_name)) ? ucfirst($records->last_name) : '';
			$is_gift 	= (isset($records->is_gift) && !empty($records->is_gift)) ? "Yes" : "No";
			
			$currentOrderStatus = 'Pending'; //status === 1 means pending
			if($records->status === 3){
				$currentOrderStatus = 'Shipped';
			} elseif($records->status === 2){
				$currentOrderStatus = 'Confirmed';
			} elseif($records->status === -1){
				$currentOrderStatus = 'Cancelled';
			}
		
			$points = 0;
			if(!empty($records->value) && !empty($records->quantity))
			{
				$points = $records->value / $records->quantity ." ( ".$records->value." per Qty ) ";
			}
			
            return [
                'Product Name' => (isset($records->name)) ? $records->name : false,
                'Order Number' => (isset($records->id)) ? 'ccad-00'.$records->id : false,
				'Price' => (isset($records->price)) ? $records->code. " " .$records->price : false,
				'Value' => (isset($records->value)) ? $records->code. " " .$records->value: false,
				'Points' => (isset($points)) ? $points: false,
				'Quantity' => (isset($records->quantity)) ? $records->quantity: false,
				'Buyer Name' => $first_name ." " .$last_name,
                'Buyer Email' => (isset($records->email)) ? $records->email: false,
                'Buyer Phone' => (isset($records->phone)) ? $records->phone: false,
                'Is Gift' => (isset($is_gift)) ? $is_gift: false,
                'Address' => (isset($records->address)) ? $records->address: false,
                'City' => (isset($records->city)) ? $records->city: false,
                'Country' => (isset($records->country)) ? $records->country: false,
				'Order Date' => (isset($records->created_at)) ? date('F j, Y g:i a', strtotime($records->created_at)) : false,
                'Order Status' => (isset($currentOrderStatus)) ? $currentOrderStatus: false,
          
            ];
        }catch (\Exception $exception){
            throw $exception;
        } 	
    }
	
    public function headings(): array
    {
        return ['Product Name','Order Number','Price','Value','Points','Quantity','Buyer Name','Buyer Email','Buyer Phone','Is Gift','Address','City','Country','Order Date','Order Status'];
    }

	public function collection()
    {
		$search = (!empty($this->param) && isset($this->param['q']) && !empty($this->param['q'])) ? $this->param['q'] : false;
		$id 	= (!empty($this->param) && isset($this->param['id']) && !empty($this->param['id'])) ? Helper::customDecrypt($this->param['id']) : false;
		
		$data = ProductOrder::select(
										'product_orders.first_name','product_orders.last_name','product_orders.email','product_orders.value','product_orders.status','currencies.code',
										'product_orders.phone','product_orders.is_gift','product_orders.address','product_orders.city','product_orders.country','t1.name',
										'product_orders.quantity','product_orders.created_at','product_orders.id','t2.price'
									)
							->leftJoin('products as t1', "t1.id","=","product_orders.product_id")
							->leftJoin('product_denominations as t2', "t2.id","=","product_orders.denomination_id")
							->leftJoin('currencies', "currencies.id","=","t1.currency_id");
							
		if(isset($id) && !empty($id))
			$data->where('product_orders.id',$id);
			
		if(isset($search) && !empty($search))
		{
			$data->where(function($query) use ($search){
				$query->where('product_orders.first_name', 'LIKE', "%{$search}%")
				->orWhere('product_orders.last_name', 'LIKE', "%{$search}%")
				->orWhere('product_orders.email', 'LIKE', "%{$search}%")
				->orWhere('t1.name', 'LIKE', "%{$search}%")
				->orWhereRaw("concat(product_orders.first_name, ' ', product_orders.last_name) LIKE '%{$search}%' ");
			});
		}				
			
		$data->orderBy('product_orders.created_at', 'DESC');
		//$data->distinct()->orderBy('product_orders.created_at', 'DESC');
				
		$result = $data->get();
						
		return $result;
    }

}
