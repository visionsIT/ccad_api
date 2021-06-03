<?php namespace Modules\Reward\Exports;

use Modules\Reward\Models\ProductOrder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use Carbon\Carbon;

/**
 * Class OrdersExports
 * @package Modules\Reward\Exports
 */
class OrdersExports implements WithHeadings, WithMapping ,FromCollection
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
				$points = $records->value / $records->quantity;
				
            return [
                'Order Number' => (isset($records->id)) ? 'ccad-00'.$records->id : false,
                'Order Date' => (isset($records->created_at)) ? date('F j, Y g:i a', strtotime($records->created_at)) : false,
                'Order Status' => (isset($currentOrderStatus)) ? $currentOrderStatus : false,
				'Employee Name' => $first_name ." " .$last_name,
                'Email' => (isset($records->email)) ? $records->email: false,
                'Product Name' => (isset($records->name)) ? $records->name : false,
                'Value' => (isset($records->value)) ? $records->code. " " .$records->value: false,
                'Quantity' => (isset($records->quantity)) ? $records->quantity: false,
                'Points' => (isset($points)) ? (string)$points: false,
				'Price' => (isset($records->price)) ? $records->code. " " .$records->price: false,
            ];
        }catch (\Exception $exception){
            throw $exception;
        } 	
    }
	
    public function headings(): array
    {
        return ['Order Number','Order Date','Order Status', 'Employee Name', 'Email','Product Name','Value','Quantity','Points','Price'];
    }

	public function collection()
    {
		$search = (!empty($this->param) && isset($this->param['q']) && !empty($this->param['q'])) ? $this->param['q'] : false;
		
		$data = ProductOrder::select('product_orders.id','product_orders.status','product_orders.first_name','product_orders.last_name','product_orders.email','product_orders.quantity','product_orders.value','product_orders.created_at','t1.name','t2.price','currencies.code')
							->leftJoin('products as t1', "t1.id","=","product_orders.product_id")
							->leftJoin('product_denominations as t2', "t2.id","=","product_orders.denomination_id")
							->leftJoin('currencies', "currencies.id","=","t1.currency_id");
							
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
