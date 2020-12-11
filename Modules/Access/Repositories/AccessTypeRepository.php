<?php namespace Modules\Access\Http\Repositories;

use App\Repositories\Repository;
use Modules\Access\Models\AccessType;

class AccessTypeRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = AccessType::class;

}
