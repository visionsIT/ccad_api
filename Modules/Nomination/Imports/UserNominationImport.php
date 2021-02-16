<?php namespace Modules\Nomination\Imports;

use Modules\Nomination\Models\UserNomination;
use Maatwebsite\Excel\Concerns\ToModel;

class UserNominationImport implements ToModel
{
    /**
     * @param array $nominations
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|UserNomination|null
     */
    public function model(array  $nominations)
    {
        return new UserNomination([
            'campaign_id'       => $nominations[1],
            'sender_email'      => $nominations[2] ?? '',
            'receiver_email'    => $nominations[3],
            'l1_email'          => $nominations[4] ?? '',
            'l2_email'          => $nominations[5],
            'receiver_group'    => $nominations[6],
            'value_category'    => $nominations[7] ?? '',
            'value'             => $nominations[8] ?? '',
            'reason_nomination' => $nominations[9] ?? '',
            'status'            => $nominations[10] ?? '',
            'pending_approver'  => $nominations[11] ?? '',
            'reason_decline'    => $nominations[12] ?? '',
            'created'           => $nominations[13] ?? '',
            'updated'           => $nominations[14] ?? '',
        ]);
    }
}
