<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Http\Requests\UserNomination\GetRequest;
use Modules\Nomination\Models\UserNomination;

class UserNominationRepository extends Repository
{
    protected $modeler = UserNomination::class;

    public function filter($data , $pagination_count)
    {
        $query = (new $this->modeler)->query();


        if (isset($data['keyword'])) {
            $query->where('name', 'like' ,  '%' . $data['keyword'] . '%');
        }

        return $query->paginate($pagination_count);
    }

    public function getDesc($campaignID)
    {
        return $this->modeler->where('campaign_id',$campaignID)->orderBy('id','desc')->paginate(12);
    }

    /**
     * @param GetRequest $request
     * @return mixed
     */
    public function getWithDateRange(GetRequest $request)
    {
        return $this->modeler::whereHas('nominee')
            ->with([
                'nominee',
                'nominee.account',
                'nominee.program',
                'nominee.account.department',
                'approver',
                'account',
                'type'
            ])
            ->when($request->has('statuses'), function ($query) use($request){
                return $query
                    ->ofStatuses( $request->get('statuses'));
            })
            ->when($request->has('start_date'), function ($query) use($request){
                return $query->where('updated_at', '>=', $request->start_date);
            })
            ->when($request->has('end_date'), function ($query) use($request){
                return $query->where('updated_at', '<=', $request->end_date);
            })
            ->active()
            ->latest()
            ->paginate(10);
    }

    public function filterRecords($data) {
        $search = $data['q'];
        $orderBy = $data['order'];
        $col = $data['col'];
        $from = $data['from'];
        $to = $data['to'] . ' 23:59:00';

        $query = UserNomination::join('program_users as t1', "t1.account_id","=","user_nominations.account_id")
            ->join('program_users as t2', "t2.account_id","=","user_nominations.user")
            ->select("user_nominations.*");


        if(isset($search)) {
            $query->where(function($query1) use ($search){
                $query1->where('t1.first_name', 'LIKE', "%{$search}%")
                ->orWhere('t1.email', 'LIKE', "%{$search}%")
                ->orWhere('t2.first_name', 'LIKE', "%{$search}%")
                ->orWhere('t2.email', 'LIKE', "%{$search}%");
            });
        }

        if(isset($from) && isset($to)) {
            $query->whereBetween('user_nominations.created_at', [$from, $to]);
        }

        if(isset($col)) {
            if($col == 'nomination_name') {
                $query->orderBy('t2.first_name',$orderBy);
            } else if ($col == 'nominator_name') {
                $query->orderBy('t1.first_name',$orderBy);
            } else if($col == 'nomination_email') {
                $query->orderBy('t2.email',$orderBy);
            } else if($col == 'nominator_email') {
                $query->orderBy('t1.email',$orderBy);
            } else if($col == 'created_date_time') {
                $query->orderBy('user_nominations.created_at',$orderBy);
            } else {
                $query->orderBy('user_nominations.id',$orderBy);
            }
        } else {
            $query->orderBy('user_nominations.id',$orderBy);
        }
        return $query->paginate(12);
    }
}
