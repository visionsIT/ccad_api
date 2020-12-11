<?php namespace Modules\User\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\User\Models\ProgramUsers;
use Maatwebsite\Excel\Concerns\ToModel;

class UserImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $users
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|ProgramUsers|null
     */
    public function model(array  $users)
    {
        return  new ProgramUsers($users);
    }
}
