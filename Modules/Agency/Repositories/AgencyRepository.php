<?php namespace Modules\Agency\Http\Repositories;

use App\Repositories\Repository;
use Modules\Agency\Models\Agency;

class AgencyRepository extends Repository
{
    protected $modeler = Agency::class;
}
