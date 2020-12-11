<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\AwardsLevel;


class AwardsLevelRepository extends Repository
{
    protected $modeler = AwardsLevel::class;
}
