<?php
namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TdsTransactionsExport implements FromQuery, WithHeadings, WithMapping, Responsable
{
    use Exportable;

    protected $query;
    private $fileName = 'tds_transactions.xlsx';

    public function __construct(Builder $query, string $fileName = null)
    {
        $this->query = $query;
        if ($fileName) $this->fileName = $fileName;
    }

    public function query()
    {
        return $this->query->with(['company', 'account', 'category']);
    }

    public function headings(): array
    {
        return ['Date', 'Company', 'Party', 'Invoice Amount', 'TDS %', 'TDS Held', 'Bank Received'];
    }

    public function map($t): array
    {
        return [
            optional($t->date)->toDateString() ?? '',
            optional($t->company)->name ?? '—',
            $t->name ?? '—',
            round($t->invoice_amount ?? 0, 2),
            ($t->tds_rate ?? 0) . '%',
            round($t->tds ?? 0, 2),
            round($t->amount ?? 0, 2),
        ];
    }
}
