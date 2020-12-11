<?php namespace Modules\Reward\Imports;

use Modules\User\Models\ProgramUsers;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductsImport implements ToModel
{
    /**
     * @param array $users
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|ProgramUsers|null
     */
    public function model(array  $users)
    {
        return new ProgramUsers([
            'program_id'     => $users[1],
            'title'     => $users[2] ?? '',
            'first_name'     => $users[3],
            'last_name'     => $users[4] ?? '',
            'email'    => $users[5],
            'username'     => $users[6],
            'company'     => $users[7] ?? '',
            'job_title'     => $users[8] ?? '',
            'address_1'     => $users[9] ?? '',
            'address_2'     => $users[10] ?? '',
            'town'     => $users[11] ?? '',
            'postcode'     => $users[12] ?? '',
            'country'     => $users[13] ?? '',
            'telephone'     => $users[14] ?? '',
            'mobile'     => $users[15] ?? '',
            'date_of_birth'     => $users[16] ?? '',
            'communication_preference' => $users[17] ?? 'email',
            'language'     => $users[18] ?? 'en',
        ]);
    }
}
