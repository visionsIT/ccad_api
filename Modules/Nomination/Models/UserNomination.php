<?php

namespace Modules\Nomination\Models;

use Modules\Account\Models\Account;
use Illuminate\Database\Eloquent\Model;

class UserNomination extends Model
{
    protected   $guarded = [];
    const       TEAM_NOMINATION = 1;
    const       USER_NOMINATION = 0;
    const       CLAIM_NOMINATION = 2;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo //
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'account_id','account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nominated_account(): \Illuminate\Database\Eloquent\Relations\BelongsTo //
    {
        return $this->belongsTo(Account::class, 'user');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account_has_role(): \Illuminate\Database\Eloquent\Relations\BelongsTo //
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account_name(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_relation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'user','account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'user');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NominationType::class, 'value');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AwardsLevel::class, 'points');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Nomination::class, 'nomination_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaignid(): \Illuminate\Database\Eloquent\Relations\BelongsTo //
    {
        return $this->belongsTo(ValueSet::class, 'campaign_id');
    }

    public function campaignSetting(): \Illuminate\Database\Eloquent\Relations\BelongsTo //
    {
        return $this->belongsTo(CampaignSettings::class, 'campaign_id', 'campaign_id');
    }


    public function sendEmail($nomination_service){
        $sender = $this->account->name;
        $sender_email = $this->account->email;
//        $account_name = $this->account_name->name;
        $user = $this->user_relation->email;
        $user_name = $this->user_relation->first_name;
//        $value = $this->type->name;
try {
    $value = $this->type->name;
    // run your code here
}
catch (exception $e) {
    //code to handle the exception
}
//        $level = optional($this->level)->name; //todo understand where point is a foreign key
        $level = optional($this->level)->points; //todo understand where point is a foreign key
        $reason = $this->reason;

        // confirm nominator

        $subject ="Cleveland Clinic Abu Dhabi - Nomination submitted ";

        $message ="Thank you for your nomination! We will inform you if the nomination is approved.";

        $nomination_service->sendmail($sender_email,$subject,$message);


        $subject = "Cleveland Clinic Abu Dhabi - Nomination for approval";

        $link = "javascript:void(0);";

        $message = "Please approve {$user_name} nomination for the {$value} value which has been submitted by {$sender} for the following reason: {$reason} \n\r <br> \n\r <br>";

        $message .="Once approved {$user_name} will receive {$level} points to their account. \n\r <br> \n\r <br> ";


        //$message .= "Dear {$nominated_by_group_name}, please approve \n\r <br>";

        $message .= "<a href=".$link.">Click here to approve this nomination</a> <br>";

        $message .= "Please approve or decline only nomination for people reporting to you \n\r <br>";

        //$email = 'rohanprabath@gmail.com';
        $nomination_service->sendmail($email,$subject,$message);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nominee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'user');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class, 'approver_account_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    /**
     * @param $query
     * @return mixed
     */
    public function scopeOfStatuses($query, $statuses = null)
    {
        return $query->where('level_1_approval', $statuses);
    }

}
