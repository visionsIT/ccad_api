<?php namespace Modules\User\Exports;


use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\User\Models\ProgramUsers;
use Maatwebsite\Excel\Concerns\FromCollection;
use DB;
/**
 * Class UsersExport
 * @package Modules\User\Exports
 */
class UserExport implements FromCollection, WithHeadings
{
    protected $param;

    public function __construct($param = '')
    {
       $this->param = $param;
    }
    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|ProgramUsers[]
     */
    public function collection()
    {
        if($this->param == ''){
            return ProgramUsers::where('is_active', 1)
                     ->select([
                        'program_users.id as id',
                        'program_users.first_name',
                        'program_users.email',
                        'program_users.company',
                        'program_users.job_title',
                        'program_users.country',
                        'program_users.point_balance',
                        'program_users.date_of_birth',
                        DB::raw("DATE_FORMAT(accounts.last_login, '%M %d, %Y %H:%i:%s') as last_login")
                    ])
                    ->join('accounts','program_users.account_id','accounts.id')
                    ->get();
        } else {
            $search = $this->param['search'];
            $column = $this->param['column'];
            $order = $this->param['order'];

            $getUserList = ProgramUsers::join('accounts','program_users.account_id','accounts.id')->where('program_users.first_name', 'like', '%' . $search . '%');
            if($search != ''){
                $getUserList = $getUserList->orwhere('program_users.last_name', 'like', '%' . $search . '%')
                ->orwhere('program_users.email', 'like', '%' . $search . '%')
                ->orwhere('program_users.job_title', 'like', '%' . $search . '%');
            }
            $getUserList = $getUserList->orderBy($column, $order)->select([
                'program_users.id',
                'program_users.first_name',
                'program_users.email',
                'program_users.company',
                'program_users.job_title',
                'program_users.country',
                'program_users.point_balance',
                'program_users.date_of_birth',
                DB::raw("DATE_FORMAT(accounts.last_login, '%M %d, %Y %H:%i:%s') as last_login")
            ])->get();

            return $getUserList;
        }
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return ['#','first_name', 'email', 'company', 'job_title', 'country', 'point_balance', 'date_of_birth','last_login'];
    }
}
