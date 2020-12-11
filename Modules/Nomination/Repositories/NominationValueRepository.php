<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\NominationValue;

class NominationValueRepository extends Repository
{
    protected $modeler = NominationValue::class;

    public function get_nomination($nomination_id)
    {
        return NominationValue::where('nomination_id', $nomination_id)->get();
    }

}
