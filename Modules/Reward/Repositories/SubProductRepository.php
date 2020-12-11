<?php namespace Modules\Reward\Repositories;

use App\Repositories\Repository;
use Modules\Reward\Models\SubProduct;

class SubProductRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = SubProduct::class;
}
