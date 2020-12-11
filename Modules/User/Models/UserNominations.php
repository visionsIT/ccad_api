<?php


namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;


class UserNominations extends Model
{
    protected $fillable = [ 'user', 'account_id', 'approver_account_id', 'nomination_id', 'value', 'points', 'reason', 'attachments', 'level_1_approval', 'level_2_approval', 'project_name', 'team_nomination', 'reject_reason', 'created_at', 'updated_at', 'is_active' ];
}
