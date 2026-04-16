<?php

// app/Http/Controllers/Admin/DashboardController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function periodRange(): array
    {
        $period = session('active_period') ?: now()->format('Y-m'); // "YYYY-MM"
        try { $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth(); }
        catch (\Throwable $e) { $start = now()->startOfMonth(); }
        $end = $start->copy()->endOfMonth();
        return [$start->toDateString(), $end->toDateString()];
    }

    public function index(Request $request)
    {
        [$start, $end] = $this->periodRange();

        $cid      = session('active_company_id');    // null => admin "All"
        $currency = optional(Company::find($cid))->currency_symbol ?? '₹';

        $isAdmin  = (bool) $request->user()->is_admin;
        $currency = '';
        if (!$isAdmin || $cid) {
            $currency = optional(Company::find((int)$cid))->currency_symbol ?: '₹';
        }

        $base = Transaction::query()->whereBetween('date', [$start, $end]);

        if (!$isAdmin) {
            $base->where('company_id', (int)$cid);
        } elseif ($cid) {
            $base->where('company_id', (int)$cid);
        }

        // Totals
        $incomeTotal  = (clone $base)->where('type', 'Income')->sum('amount');
        $expenseTotal = (clone $base)->where('type', 'Expense')->sum('amount');
        $balance      = $incomeTotal - $expenseTotal;

        // Income by category (for donut + table)
        $incomeByCat = (clone $base)
            ->where('type', 'Income')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get()
            ->map(fn($r) => ['id' => $r->category?->id, 'name' => $r->category?->name ?? '—', 'total' => (float)$r->total])
            ->sortByDesc('total')->values();

        // Expense by category
        $expenseByCat = (clone $base)
            ->where('type', 'Expense')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get()
            ->map(fn($r) => ['id' => $r->category?->id, 'name' => $r->category?->name ?? '—', 'total' => (float)$r->total])
            ->sortByDesc('total')->values();

        // Account “amount left” = net change this month per account (Income - Expense)
        $accountsNet = (clone $base)
            ->select('account_id', DB::raw("SUM(CASE WHEN type='Income' THEN amount ELSE -amount END) as total"))
            ->groupBy('account_id')
            ->with('account:id,name')
            ->get()
            ->map(fn($r) => ['id' => $r->account?->id,'name' => $r->account?->name ?? '—', 'total' => (float)$r->total])
            ->sortByDesc('total')->values();

        // Cash flow bar (just totals for the month)
        $cashFlow = [
            'income'  => (float)$incomeTotal,
            'expense' => (float)$expenseTotal,
        ];

        // Friendly month label
        $monthLabel = Carbon::parse($start)->isoFormat('MMMM YYYY'); // e.g., "August 2025"

        // ---- Financial Year derived from active_period ----
        $period = session('active_period') ?: now()->format('Y-m'); // "YYYY-MM"
        try { [$yy, $mm] = array_map('intval', explode('-', $period)); }
        catch (\Throwable $e) { $yy = now()->year; $mm = now()->month; }

        $fyStartYear = ($mm >= 4) ? $yy : $yy - 1;                    // Apr–Dec -> same year; Jan–Mar -> previous
        $fyStart     = Carbon::create($fyStartYear, 4, 1)->startOfMonth();
        $fyEnd       = $fyStart->copy()->addYear()->subDay();         // through Mar 31 next year
        $fyLabel     = 'April '.$fyStartYear.' – March '.($fyStartYear+1);

        $today = now()->toDateString();

        // Aggregate per month in FY
        $base = Transaction::query()
            ->where('company_id', $cid)
            ->whereBetween('date', [$fyStart->toDateString(), $fyEnd->toDateString()])
            ->where(function ($q) use ($today) {
                $q->where('description', '!=', 'Opening Balance')
                ->orWhereNull('description')
                ->orWhereDate('date', '<=', $today);
            });

        $rows = (clone $base)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') ym,
                        SUM(CASE WHEN type='Income'  THEN amount ELSE 0 END) AS income,
                        SUM(CASE WHEN type='Expense' THEN amount ELSE 0 END) AS expense")
            ->groupBy('ym')->orderBy('ym')->get()->keyBy('ym');

        $fyLabels = $fyIncome = $fyExpense = $fyCash = [];
        for ($i = 0; $i < 12; $i++) {
            $m  = $fyStart->copy()->addMonths($i);
            $ym = $m->format('Y-m');
            $fyLabels[]  = $m->format('F');                           // April, May, ...
            $inc         = (float) ($rows[$ym]->income  ?? 0);
            $exp         = (float) ($rows[$ym]->expense ?? 0);
            $fyIncome[]  = $inc;
            $fyExpense[] = $exp;
            $fyCash[]    = $inc - $exp;
        }

        $fyTotals = [
            'income'  => array_sum($fyIncome),
            'expense' => array_sum($fyExpense),
            'cash'    => array_sum($fyCash),
        ];
        $fyAverages = [
            'income'  => $fyTotals['income']  / 12,
            'expense' => $fyTotals['expense'] / 12,
            'cash'    => $fyTotals['cash']    / 12,
        ];

        $q = Category::query()
            ->where('categories.on_dashboard', true)
            ->where('categories.dashboard_period', 'all_time')
            // if not admin OR admin with a selected company -> scope to that company
            ->when(!$isAdmin || $cid, fn($qq) => $qq->where('categories.company_id', (int)$cid))
            ->leftJoin('transactions as t', function ($join) {
                $join->on('t.category_id', '=', 'categories.id')
                    ->on('t.company_id',  '=', 'categories.company_id'); // <- keep same company
            })
            ->select([
                'categories.id',
                'categories.name',
                'categories.type',
                'categories.company_id',
                \DB::raw("COALESCE(SUM(CASE WHEN t.type='Expense' THEN -t.amount ELSE t.amount END), 0) as total_amount"),
                \DB::raw('COUNT(t.id) as tx_count'),
            ])
            ->groupBy('categories.id', 'categories.name', 'categories.type', 'categories.company_id')
            ->orderBy('categories.name');


        $categories = $q->get();


        $mq = Category::query()
            ->where('categories.on_dashboard', true)
            ->where('categories.dashboard_period', 'monthly')   // ← only monthly tiles
            ->when(!$isAdmin || $cid, fn ($qq) => $qq->where('categories.company_id', (int) $cid))
            ->leftJoin('transactions as t', function ($join) use ($start, $end) {
                $join->on('t.category_id', '=', 'categories.id')
                    ->on('t.company_id',  '=', 'categories.company_id')
                    ->whereBetween('t.date', [$start, $end]);
            })
            ->select([
                'categories.id',
                'categories.name',
                'categories.type',
                'categories.company_id',
                \DB::raw("COALESCE(SUM(CASE WHEN t.type='Expense' THEN -t.amount ELSE t.amount END), 0) as total_amount"),
                \DB::raw('COUNT(t.id) as tx_count'),
            ])
            ->groupBy('categories.id', 'categories.name', 'categories.type', 'categories.company_id')
            ->orderBy('categories.name');


        $monthlycategories = $mq->get();

        $start = Carbon::create($yy,$mm,1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $files = File::query()
                    ->when($isAdmin && $cid, fn($q)=>$q->where('company_id', (int)$cid))
                    ->when(!$isAdmin && $cid, fn($q)=>$q->where('company_id', (int)$cid))
                    ->whereBetween('period', [$start->toDateString(), $end->toDateString()])
                    ->latest()
                    ->limit(20)
                    ->get();

        $totalReceivable = Transaction::where('company_id', $cid)->where('type', 'Income')->where('status', 'pending')->sum('amount');
        $overdue         = Transaction::where('company_id', $cid)->where('type', 'Income')->where('status', 'pending')->where('date', '<', Carbon::now()->subDays(30))->sum('amount');
        $settled         = Transaction::where('company_id', $cid)->where('type', 'Income')->where('status', 'paid')->whereBetween('date', [$start, $end])->sum('amount');
        $receivables     = Transaction::where('company_id', $cid)->where('type', 'Income')->where('status', 'pending')->with(['company','account','category'])->get();
        $dashAccounts    = Category::where('company_id', $cid)->where('type', 'Account')->orderBy('name')->get();
        $dashInexCats    = Category::where('company_id', $cid)->where('type', 'Income/Expense')->orderBy('name')->get();

        $totalPayable   = Transaction::where('company_id', $cid)->where('type', 'Expense')->where('status', 'pending')->sum('amount');
        $overduePayable = Transaction::where('company_id', $cid)->where('type', 'Expense')->where('status', 'pending')->where('date', '<', Carbon::now()->subDays(30))->sum('amount');
        $paidThisMonth  = Transaction::where('company_id', $cid)->where('type', 'Expense')->where('status', 'paid')->whereBetween('date', [$start, $end])->sum('amount');
        $payables       = Transaction::where('company_id', $cid)->where('type', 'Expense')->where('status', 'pending')->with(['company','account','category'])->get();

         // GST/TDS Widget
        $gstMonths = Transaction::where('company_id', $cid)
            ->where('transaction_type', 'gst')
            ->selectRaw("DATE_FORMAT(date,'%Y-%m') as ym")
            ->groupBy('ym')->orderByDesc('ym')->pluck('ym');

        $gstFilter = $request->input('gst_month', 'all');

        $gstBase = Transaction::where('company_id', $cid)->where('transaction_type', 'gst');
        $tdsBase = Transaction::where('company_id', $cid)->where('transaction_type', 'tds');
        if ($gstFilter !== 'all') {
            $gstBase->whereRaw("DATE_FORMAT(date,'%Y-%m') = ?", [$gstFilter]);
            $tdsBase->whereRaw("DATE_FORMAT(date,'%Y-%m') = ?", [$gstFilter]);
        }

        $gstCollected  = (clone $gstBase)->where('type','Income')->sum('gstLocked');
        $gstPaid       = (clone $gstBase)->where('type','Expense')->sum('gstLocked');
        $netGst        = $gstCollected - $gstPaid;
        $tdsHeldPure   = (clone $tdsBase)->sum('tds');
        $tdsOnGst      = (clone $gstBase)->sum('tds_on_gst');
        $totalTdsRefundable = $tdsHeldPure + $tdsOnGst;

        $gstTransactions = (clone $gstBase)->with(['company','account','category'])->orderByDesc('date')->get();
        $tdsTransactions = (clone $tdsBase)->with(['company','account','category'])->orderByDesc('date')->get();

        return view('admin.dashboard.index', [
            'monthLabel'        => $monthLabel,
            'currency'          => $currency,
            'isAdminAll'        => $isAdmin && !$cid,
            'incomeTotal'       => $incomeTotal,
            'expenseTotal'      => $expenseTotal,
            'balance'           => $balance,
            'incomeByCat'       => $incomeByCat,
            'expenseByCat'      => $expenseByCat,
            'accountsNet'       => $accountsNet,
            'cashFlow'          => $cashFlow,
            'fyStartYear'       => $fyStartYear,
            'fyLabel'           => $fyLabel,
            'fyLabels'          => $fyLabels,
            'fyIncome'          => $fyIncome,
            'fyExpense'         => $fyExpense,
            'fyCash'            => $fyCash,
            'fyTotals'          => $fyTotals,
            'fyAverages'        => $fyAverages,
            'categories'        => $categories,
            'monthlycategories' => $monthlycategories,
            'files'             => $files,
            'totalReceivable'   => $totalReceivable,
            'overdue'           => $overdue,
            'settled'           => $settled,
            'receivables'       => $receivables,
            'dashAccounts'      => $dashAccounts,
            'dashInexCats'      => $dashInexCats,
            'totalPayable'      => $totalPayable,
            'overduePayable'    => $overduePayable,
            'paidThisMonth'     => $paidThisMonth,
            'payables'          => $payables,
            'gstMonths'         => $gstMonths,
            'gstFilter'         => $gstFilter,
            'gstCollected'      => $gstCollected,
            'gstPaid'           => $gstPaid,
            'netGst'            => $netGst,
            'tdsHeldPure'       => $tdsHeldPure,
            'tdsOnGst'          => $tdsOnGst,
            'totalTdsRefundable'=> $totalTdsRefundable,
            'gstTransactions'   => $gstTransactions,
            'tdsTransactions'   => $tdsTransactions
        ]);
    }

    // Modal content: all transactions for a single category (all time)
    public function categoryTransactions(Request $request, Category $category)
    {
        $user = $request->user();
        $cid  = session('active_company_id');

        // authorize: if non-admin, must match company; if admin and viewing “All”, allow; if admin and specific company, must match.
        if (!$user->is_admin) {
            abort_unless((int)$category->company_id === (int)$cid, 403);
        } elseif ($cid) { // admin with a specific active company
            abort_unless((int)$category->company_id === (int)$cid, 403);
        }

        $q = Transaction::query()
            ->with(['account','company']) // if you show these in list
            ->where('category_id', $category->id);

        if ($request->boolean('month')) {
            // use the same active month you show on the dashboard
            [$start, $end] = $this->periodRange();
            $q->whereBetween('date', [$start, $end]);
        }

        $tx = $q->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        $currency = optional(Company::find((int)$cid))->currency_symbol ?: '₹';
        return view('admin.dashboard.partials.category-transactions-modal', [
            'category' => $category,
            'transactions' => $tx,
            'is_account' => false,
            'currency' => $currency
        ]);
    }

    public function gstTransactionsModal(Request $request)
    {
        $cid      = session('active_company_id');
        $currency = optional(Company::find((int)$cid))->currency_symbol ?: '₹';
        $gstFilter = $request->input('gst_month', 'all');

        $q = Transaction::where('company_id', $cid)->where('transaction_type', 'gst');
        if ($gstFilter !== 'all') {
            $q->whereRaw("DATE_FORMAT(date,'%Y-%m') = ?", [$gstFilter]);
        }

        $transactions = $q->with(['company', 'account', 'category'])->orderByDesc('date')->get();

        return view('admin.dashboard.partials.gst-transactions-modal', [
            'transactions' => $transactions,
            'currency'     => $currency,
            'gstFilter'    => $gstFilter,
        ]);
    }

    public function tdsTransactionsModal(Request $request)
    {
        $cid      = session('active_company_id');
        $currency = optional(Company::find((int)$cid))->currency_symbol ?: '₹';
        $gstFilter = $request->input('gst_month', 'all');

        $q = Transaction::where('company_id', $cid)->where('transaction_type', 'tds');
        if ($gstFilter !== 'all') {
            $q->whereRaw("DATE_FORMAT(date,'%Y-%m') = ?", [$gstFilter]);
        }

        $transactions = $q->with(['company', 'account', 'category'])->orderByDesc('date')->get();

        return view('admin.dashboard.partials.tds-transactions-modal', [
            'transactions' => $transactions,
            'currency'     => $currency,
            'gstFilter'    => $gstFilter,
        ]);
    }

    public function accountTransactions(Request $request, Category $account)
    {
        $user = $request->user();
        $cid  = session('active_company_id');
        [$start, $end] = $this->periodRange();

        // authorize: if non-admin, must match company; if admin and viewing “All”, allow; if admin and specific company, must match.
        if (!$user->is_admin) {
            abort_unless((int)$account->company_id === (int)$cid, 403);
        } elseif ($cid) { // admin with a specific active company
            abort_unless((int)$account->company_id === (int)$cid, 403);
        }

        $q = Transaction::query()
            ->with(['category','company']) // if you show these in list
            ->where('account_id', $account->id)
            ->whereBetween('date', [$start, $end]);

        $tx = $q->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(500)
            ->withQueryString();

        $currency = optional(Company::find((int)$cid))->currency_symbol ?: '₹';
        return view('admin.dashboard.partials.category-transactions-modal', [
            'category' => $account,
            'transactions' => $tx,
            'is_account' => true,
            'currency' => $currency
        ]);
    }
}