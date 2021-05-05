<?php namespace Modules\Agency\Repositories;

use App\Repositories\Repository;
use Modules\Agency\Models\Agency;

class AgencyRepository extends Repository
{
    protected $modeler = Agency::class;
}
