<?php


namespace Modules\User\Http\Services;

use Modules\User\Models\UserNominations;


class UserNominationService
{

    public function find($id) {
        return UserNominations::where('user', '=', $id)->get();
    }
}
