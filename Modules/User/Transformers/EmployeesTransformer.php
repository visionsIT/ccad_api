<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\ProgramUsers;

class EmployeesTransformer extends TransformerAbstract
{
    /**
     * @param ProgramUsers $User
     *
     * @return array
     */
    public function transform(ProgramUsers $User): array
    {
        return [
            'id' => $User->account_id,
            'user_id' => $User->id,
            'name' => $User->first_name . ' ' . $User->last_name,
            'email' => $User->email,
            'default_department_name'   =>  (!empty($User->account->defaultDepartment)) ? $User->account->defaultDepartment->name : '',
            'teams' =>  $User->account->getTeams()
        ];
    }
}
