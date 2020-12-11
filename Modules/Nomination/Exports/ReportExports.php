<?php namespace Modules\Nomination\Exports;


use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Class ReportExports
 *
 * @package Modules\Nomination\Exports
 */
class ReportExports implements WithMapping, WithHeadings
{
    /**
     * @param mixed $report
     *
     * @return array
     */
    public function map($report) :array
    {
        dd($report);
        return [
            $report->user->account->email,
            $report->account_id,
            Date::dateTimeToExcel($report->created_at),
        ];
    }

    public function headings(): array
    {
        return [
            'Nominated User',
            'Nominated By',
            'Nominated At',
        ];
    }
}
