<?php namespace Modules\User\Exports;


use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\User\Models\ProgramUsers;
use Maatwebsite\Excel\Concerns\FromCollection;

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
                        'id',
                        'first_name',
                        'email',
                        'company',
                        'job_title',
                        'country',
                        'point_balance',
                        'date_of_birth'
                    ])
                    ->get();
        } else {
            $search = $this->param['search'];
            $column = $this->param['column'];
            $order = $this->param['order'];

            $getUserList = ProgramUsers::where('first_name', 'like', '%' . $search . '%');
            if($search != ''){
                $getUserList = $getUserList->orwhere('last_name', 'like', '%' . $search . '%')
                ->orwhere('email', 'like', '%' . $search . '%')
                ->orwhere('job_title', 'like', '%' . $search . '%');
            }
            $getUserList = $getUserList->orderBy($column, $order)->select([
                'id',
                'first_name',
                'email',
                'company',
                'job_title',
                'country',
                'point_balance',
                'date_of_birth'
            ])->get();

            return $getUserList;
        }
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return ['#','first_name', 'email', 'company', 'job_title', 'country', 'point_balance', 'date_of_birth'];
    }
}
