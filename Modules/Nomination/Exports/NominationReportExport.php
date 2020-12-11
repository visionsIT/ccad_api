<?php namespace Modules\Nomination\Exports;


use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Nomination\Models\UserNomination;

/**
 * Class ReportExports
 *
 * @package Modules\Nomination\Exports
 */
class NominationReportExport implements  WithHeadings, WithMapping ,FromCollection
{
    protected $statuses;
    protected $status;
    protected $request;
    public function __construct($status = null, $request)
    {
        $this->statuses = ['Pending', "Approved", "Decline"];
        $this->status = $status;
        $this->request = $request;
    }
    /**
     * @param $nomination
     * @return array
     * @throws \Exception
     */
    public function map($nomination) :array
    {
        try{
            return [
                'id' => $nomination->id,
                'nominee_first_name' => $nomination->nominee->first_name,
                'nominee_last_name' => $nomination->nominee->last_name,
                'nominee' => $nomination->nominee->email ,
                'nomination_date' => (new Carbon($nomination->created_at))->format('Y-m-d'),
                'nomination_name' => $nomination->type->name,
                'nomination_reason' => $nomination->level_1_approval == 1 && $nomination->reason == "It has been declined based on the current performance of the nominee." ? " ": $nomination->reason,
                'nomination_points' => $nomination->points == 1 ? 500 : $nomination->points,
                'nominator_first_name' => ucfirst($nomination->account->user->first_name).' '.ucfirst($nomination->account->user->last_name),//$nomination->account->name,
                'nominator_email' => $nomination->account->email,
                'Approved/Declined by' => $nomination->approver->email ?? '',
                'approval_status' => $this->statuses[$nomination->level_1_approval == -1 ? 2 : $nomination->level_1_approval],
                'approval_date' => (new Carbon($nomination->updated_at))->format('Y-m-d'),
            ];
        }catch (\Exception $exception){
            throw $exception;
        }

    }

    public function headings(): array
    {
        return [
            'id',
            'nominee_first_name',
            'nominee_last_name',
            'nominee',
            'nomination_date',
            'nomination_name',
            'nomination_reason',
            'nomination_points',
            'nominator_first_name',
            'nominator_email',
            'Approved/Declined by',
            'approval_status',
            'approval_date',
        ];
    }

    /**
     * @inheritDoc
     */
    public function collection()
    {
        return UserNomination::whereHas('nominee')
            ->with([
                'nominee',
                'nominee.account',
                'approver',
                'account',
                'type'
            ])
            ->when($this->status !== null, function ($query){
                return $query->where('level_1_approval', $this->status);
            })->when($this->request->has('start_date'), function ($query){
                return $query->where('updated_at', '>=', $this->request->start_date);
            })
            ->when($this->request->has('end_date'), function ($query) {
                return $query->where('updated_at', '<=', $this->request->end_date);
            })
            ->active()
            ->get();
    }

}
