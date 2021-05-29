<?php
namespace App\Exports;
use App\Report;
use Maatwebsite\Excel\Concerns\FromArray;
class ReportExport implements FromArray
{
    protected $reports;
    public function __construct(array $reports)
    {
        $this->reports = $reports;
    }
    public function array(): array
    {
        return $this->reports;
    }
}