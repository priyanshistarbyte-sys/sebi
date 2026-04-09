<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\TransactionsExport;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TransactionExportController extends Controller
{
    public function export(Category $category, Request $request)
    {
        // Build the SAME base query you use for $transactions in the view,
        // including any filters from the request (dates, account, type, search, etc.).
        $q = Transaction::query();
        if ($request->boolean('is_account')) {
            $q->where('account_id', $category->id);
        }
        else {
            $q->where('category_id', $category->id);
        }
        $q->orderByDesc('date')->orderByDesc('id');

        $period = session('active_period') ?: now()->format('Y-m'); // "YYYY-MM"
        try { $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth(); }
        catch (\Throwable $e) { $start = now()->startOfMonth(); }
        $end = $start->copy()->endOfMonth();

        $startmonthLabel = Carbon::parse($start)->isoFormat('DD-MM-YYYY');
        $endmonthLabel = Carbon::parse($end)->isoFormat('DD-MM-YYYY');

        if ($request->boolean('month')) {
            // use the same active month you show on the dashboard
            $q->whereBetween('date', [$start, $end]);

            $dateRange = "{$startmonthLabel}_to_{$endmonthLabel}";
        }
        else {
            $dateRange = "until_{$endmonthLabel}";
        }

        $format = $request->string('format', 'xlsx'); // xlsx|csv
        $file   = 'transactions_'.$category->name.'_'.$dateRange.'.'.$format;

        $export = new TransactionsExport($q, $file, $request->boolean('is_account'), $format === 'csv');

        return match ($format) {
            'csv'  => Excel::download($export, $file, \Maatwebsite\Excel\Excel::CSV),
            default => Excel::download($export, $file),
        };
    }
}
