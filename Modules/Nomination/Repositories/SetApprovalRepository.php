<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\SetApproval;


class SetApprovalRepository extends Repository
{
    protected $modeler = SetApproval::class;
}
