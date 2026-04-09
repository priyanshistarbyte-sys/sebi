<?php
namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Str;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, Responsable
{
    use Exportable;

    /** @var Builder */
    protected $query;
    protected $currencySymbol;
    protected $asCsv;
    protected $is_account;

    /** @var string */
    private $fileName = 'transactions.xlsx';

    public function __construct(Builder $baseQuery, string $fileName = null, bool $is_account = false, bool $asCsv = false)
    {
        $this->query = $baseQuery;
        if ($fileName) $this->fileName = $fileName;
        $this->is_account = $is_account;
        $this->currencySymbol = '₹';
        $this->asCsv = $asCsv;
    }

    public function getCsvSettings(): array
    {
        return [
            'input_encoding'  => 'UTF-8',
            'output_encoding' => 'UTF-8',
            'use_bom'         => true,  // ensures Excel detects UTF-8
            'delimiter'       => ',',
        ];
    }

    public function query()
    {
        return $this->query->with(['account', 'category', 'company']); // eager for mapping
    }

    public function headings(): array
    {
        if($this->is_account){
            return ['Date', 'Category', 'Amount', 'Description'];
        }
        else {
            return ['Date', 'Account', 'Amount', 'Description'];
        }
    }

    public function map($t): array
    {
        $isExpense = $t->type === 'Expense';
        $sign = $isExpense ? -1 : 1;
        $this->currencySymbol = optional($t->company)->currency_symbol ?? '₹';

        if($this->is_account){
            return [
                optional($t->date)->toDateString() ?? optional($t->created_at)->toDateString(),
                optional($t->category)->name ?? '—',
                round(($t->amount ?? 0) * $sign, 2),
                trim(Str::of($t->description ?? '')->replace(["\r", "\n"], ' ')->stripTags()),
            ];
        }
        else {
            return [
                optional($t->date)->toDateString() ?? optional($t->created_at)->toDateString(),
                optional($t->account)->name ?? '—',
                round(($t->amount ?? 0) * $sign, 2),
                trim(Str::of($t->description ?? '')->replace(["\r", "\n"], ' ')->stripTags()),
            ];
        }
    }

    public function columnFormats(): array
    {
        if ($this->asCsv) {
            return [];
        }

        // Amount column is C in the XLSX mapping
        $fmt = sprintf('%s#,##0.00;[Red]-%s#,##0.00', $this->currencySymbol, $this->currencySymbol);
        return ['C' => $fmt];
    }
}
