<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\NominationDecline;


class NominationDeclineRepository extends Repository
{
    protected $modeler = NominationDecline::class;
}
