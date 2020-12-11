<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\Nomination;
use Modules\Nomination\Models\NominationType;
use Modules\Nomination\Models\ClaimType;

class NominationRepository extends Repository
{
    protected $modeler = Nomination::class;

    public function filter($data , $pagination_count)
    {
        $query = (new $this->modeler)->query();


        if (isset($data['keyword'])) {
            $query->where('name', 'like' ,  '%' . $data['keyword'] . '%');
        }

        return $query->paginate($pagination_count);
    }

    public function get_nomination($value_set)
    {
        $Nomination_array["NominationType"]=NominationType::where('value_set', $value_set)->get();
        $Nomination_array["ValueSet"]=ValueSet::where('id', $value_set)->get();

        return $Nomination_array;
    }

    public function getClaimType()
    {
        return  ClaimType::select()->get();
    }

}
