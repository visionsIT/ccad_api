<?php namespace Modules\Program\Repositories;

use App\Repositories\Repository;
use Modules\Program\Models\ProgramsDomain;


class DomainRepository extends Repository
{
    protected $modeler = ProgramsDomain::class;
}
