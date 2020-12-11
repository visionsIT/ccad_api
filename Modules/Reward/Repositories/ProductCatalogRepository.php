<?php namespace Modules\Reward\Repositories;

use App\Repositories\Repository;
use Modules\Reward\Models\ProductCatalog;

class ProductCatalogRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = ProductCatalog::class;
}
