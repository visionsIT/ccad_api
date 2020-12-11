<?php namespace Modules\Reward\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Reward\Models\ProductOrder;
use Maatwebsite\Excel\Concerns\ToModel;

class OrderImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $users
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|ProgramUsers|null
     */
    public function model(array  $orders)
    {
        return  new ProductOrder($orders);
    }
}
