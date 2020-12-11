<?php namespace Modules\Reward\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Reward\Models\ProductCatalog;

class CatalogImport implements ToModel
{
    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|ProgramUsers|null
     */
    public function model(array  $data)
    {
        return new ProductCatalog([
            'name'     => $data[21],
        ]);
    }
}
