<?php namespace Modules\Agency\Repositories;

use App\Repositories\Repository;
use Modules\Agency\Models\AgenciesAdmin;

class AgencyAdminsRepository extends Repository
{
    protected $modeler = AgenciesAdmin::class;

}
