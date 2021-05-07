<?php namespace Modules\User\Repositories;

use App\Repositories\Repository;
use Modules\User\Models\UsersGoalItem;
use Modules\User\Models\UsersPoint;

class GoalItemRepository extends Repository
{
    protected $modeler = UsersGoalItem::class;

    /**
     * @param $user_id
     * @param $data
     *
     * @return mixed
     */
    public function updateOrCreate($user_id, $data)
    {
        return $this->modeler->updateOrCreate([ 'user_id' => $user_id ], $data);
    }

    public function removeGoalItem($user_id, $data)
    {
         $this->modeler->where('user_id',$user_id)->delete();
		 return $this->modeler->where('user_id', $user_id)->first();
    }

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function getUserGoalItem($user_id)
    {
        return $this->modeler->where('user_id', $user_id)->first();
    }
}
