<?php namespace Modules\Agency\Http\Repositories;

use App\Repositories\Repository;
use Modules\Agency\Models\Catalogue;

class CatalogueRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = Catalogue::class;
}
