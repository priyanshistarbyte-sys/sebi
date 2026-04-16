<?php
namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Str;

class GstTransactionsExport implements FromQuery, WithHeadings, WithMapping, Responsable
{
    use Exportable;

    protected $query;
    private $fileName = 'gst_transactions.xlsx';

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
        return ['Date', 'Company', 'Party', 'Direction', 'Base', 'GST', 'TDS', 'Bank Hit'];
    }

    public function map($t): array
    {
        return [
            optional($t->date)->toDateString() ?? '',
            optional($t->company)->name ?? '—',
            $t->name ?? '—',
            $t->type === 'Income' ? 'IN' : 'OUT',
            round($t->base ?? 0, 2),
            round($t->gstLocked ?? 0, 2),
            round($t->tds ?? 0, 2),
            round($t->gstLocked ?? 0, 2),
        ];
    }
}
