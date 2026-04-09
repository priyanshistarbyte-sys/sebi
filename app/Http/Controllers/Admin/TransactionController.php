<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Company;
use App\Services\OpeningBalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TransactionController extends Controller
{
    private array $types = ['Income','Expense'];

    // Helper to get start/end of the active month
    private function activePeriodRange(): array
    {
        $period = session('active_period') ?: now()->format('Y-m'); // "YYYY-MM"
        // safe parse; default to current month on failure
        try { $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth(); }
        catch (\Throwable $e) { $start = now()->startOfMonth(); }
        $end = $start->copy()->endOfMonth();
        return [$start->toDateString(), $end->toDateString()];
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $cid  = session('active_company_id');

        $currencySymbol = '';
        if ($request->user()->is_admin) {
            if ($cid) {
                $currencySymbol = optional(Company::find((int)$cid))->currency_symbol ?: '';
            } // else stay blank when viewing All companies
        } else {
            $currencySymbol = optional(Company::find((int)$cid))->currency_symbol ?: '';
        }

        // --- form data (only when a concrete company is selected) ---
        $canCreate = $user->is_admin ? (bool)$cid : true; // non-admin always has one
        $companyIdForForm = $user->is_admin ? (int)($cid ?? 0) : (int)$cid;

        $inexCats = $canCreate
            ? Category::where('company_id',$companyIdForForm)->where('type','Income/Expense')->orderBy('name')->get()
            : collect();
        $accounts = $canCreate
            ? Category::where('company_id',$companyIdForForm)->where('type','Account')->orderBy('name')->get()
            : collect();

        [$startDate, $endDate] = $this->activePeriodRange();

        // --- filters (date, type, account) ---
        $rawStart    = $request->query('start_date');       // YYYY-MM-DD optional
        $rawEnd      = $request->query('end_date');         // YYYY-MM-DD optional
        $fType     = $request->query('type');       // Income|Expense
        $fAccount  = (int) $request->query('account_id'); // category id
        $searchQ   = trim((string)$request->query('q', ''));
        $perPage   = (int) $request->query('per_page', 15);

        // clamp helper: if user provided date, parse; if invalid, fallback to session bound
        try { $parsedStart = $rawStart ? Carbon::parse($rawStart)->toDateString() : null; } catch (\Throwable $e) { $parsedStart = null; }
        try { $parsedEnd   = $rawEnd   ? Carbon::parse($rawEnd)->toDateString()   : null; } catch (\Throwable $e) { $parsedEnd   = null; }

        // Ensure clamped within session month
        $clampedStart = $parsedStart && $parsedStart >= $startDate ? $parsedStart : $startDate;
        $clampedEnd   = $parsedEnd   && $parsedEnd   <= $endDate   ? $parsedEnd   : $endDate;

        // sanitize per-page (allow only certain presets, max 500)
        $allowedPerPage = [10,15,25,50,100,250,500];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $query = Transaction::query()
                    ->leftJoin('categories as cat', 'cat.id', '=', 'transactions.category_id')
                    ->leftJoin('categories as acc',   'acc.id',   '=', 'transactions.account_id')
                    ->select('transactions.*')
                    ->selectRaw('cat.name AS category_name, acc.name AS account_name');
                    

        if ($user->is_admin) {
            if ($cid) { $query->where('transactions.company_id', (int)$cid); } // specific company
            // else "All companies" (no company filter)
            $accountChoices = $cid
                ? Category::where('company_id',(int)$cid)->where('type','Account')->orderBy('name')->get()
                : Category::where('type','Account')->orderBy('name')->get();
        } else {
            $query->where('transactions.company_id', (int)$cid);
            $accountChoices = Category::where('company_id',(int)$cid)->where('type','Account')->orderBy('name')->get();
        }

        // Apply month range from session period only if no explicit date range provided
        $query->whereBetween('date', [$clampedStart, $clampedEnd]);

        // Sorting
        $sortable = ['date', 'type', 'category', 'account', 'amount'];
        $sort = $request->get('sort', 'date');
        $dir  = $request->get('dir', 'desc');
        if (!in_array($sort, $sortable)) { $sort = 'name'; }
        if (!in_array(strtolower($dir), ['asc','desc'])) { $dir = 'desc'; }

        /*$query = $query
            ->when($fDate, fn($q) => $q->whereDate('date', $fDate))
            ->when(in_array($fType, $this->types, true), fn($q) => $q->where('transactions.type', $fType))
            ->when($fAccount > 0, fn($q) => $q->where('account_id', $fAccount));*/
            // ->orderBy($sort, $dir)->orderBy('date', 'desc')
            // ->paginate(15)->withQueryString();

        $query = $query
            ->when(in_array($fType, $this->types, true), fn($q) => $q->where('transactions.type', $fType))
            ->when($fAccount > 0, fn($q) => $q->where('transactions.account_id', $fAccount))
            ->when($searchQ, function($q) use ($searchQ) {
                $q->where(function($q2) use ($searchQ) {
                    // search description, category name, account name
                    $q2->where('transactions.description', 'like', '%' . $searchQ . '%')
                    ->orWhere('cat.name', 'like', '%' . $searchQ . '%')
                    ->orWhere('acc.name', 'like', '%' . $searchQ . '%');

                    // if numeric-ish, also match amount exactly or partially
                    if (is_numeric($searchQ)) {
                        $q2->orWhere('transactions.amount', (float)$searchQ);
                    } else {
                        // attempt partial match against amount as string
                        $q2->orWhereRaw("CAST(transactions.amount AS CHAR) LIKE ?", ["%{$searchQ}%"]);
                    }
                });
            });

        switch ($sort) {
            case 'category':
                $query->orderBy('category_name', $dir);
                break;
            case 'account':
                $query->orderBy('account_name', $dir);
                break;
            case 'amount':
                $query->orderBy('transactions.amount', $dir);
                break;
            case 'type':
                $query->orderBy('transactions.type', $dir);
                break;
            case 'date':
            default:
                $query->orderBy('transactions.date', $dir);
                break;
        }
        $query->orderBy('transactions.id', 'desc');
        $transactions = $query->paginate($perPage)->withQueryString();

        [$startDate, $endDate] = $this->activePeriodRange();
        $today = now()->toDateString();
        $defaultDateForForm = ($today >= $startDate && $today <= $endDate) ? $today : $startDate;

        // categories (by company) for inline editor rows
        $companyIdsOnPage = $transactions->pluck('company_id')->unique()->values();

        $cats = Category::whereIn('company_id', $companyIdsOnPage)
            ->select('id','name','type','company_id')
            ->orderBy('name')
            ->get()
            ->groupBy('company_id');

        $categoryMap = [];
        foreach ($cats as $cid => $group) {
            $categoryMap[$cid] = [
                'InEx'  => $group->where('type','Income/Expense')->values()->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->all(),
                'Account' => $group->where('type','Account')->values()->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->all(),
            ];
        }

        // currency per company for the editor amount prefix
        $currencyByCompany = Company::whereIn('id', $companyIdsOnPage)->pluck('currency_symbol','id');

        $today = now()->toDateString();
        $defaultDateForForm = ($today >= $startDate && $today <= $endDate) ? $today : $startDate;
        return view('admin.transactions.index', [
            'canCreate'             => $canCreate,
            'inexCats'              => $inexCats,
            'accounts'              => $accounts,
            'types'                 => $this->types,
            'companyIsAll'          => $user->is_admin && !$cid, // admin on "All companies"
            'transactions'          => $transactions,
            'currencySymbol'        => $currencySymbol,
            'companyIsAll'          => $request->user()->is_admin && !$cid,
            'defaultDateForForm'    => $defaultDateForForm,
            'categoryMap'           => $categoryMap,
            'currencyByCompany'     => $currencyByCompany,
            // filters
            'fDateStart'        => ($parsedStart ? $parsedStart : null),
            'fDateEnd'          => ($parsedEnd ? $parsedEnd : null),
            'clampedStart'      => $clampedStart,
            'clampedEnd'        => $clampedEnd,
            'startDate'         => $startDate,   // session month start (for min attr)
            'endDate'           => $endDate,     // session month end (for max attr)
            'fType'             => $fType,
            'fAccount'          => $fAccount,
            'fSearch'           => $searchQ,
            'perPage'           => $perPage,
            'defaultDateForForm'=> $defaultDateForForm,
            'accountChoices'        => $accountChoices,
            'isAdmin'               => (bool)$user->is_admin,
            'sort'                  => $sort,
            'dir'                   => $dir
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $cid  = session('active_company_id');

        // must have a concrete company to create
        if (!$cid) abort(422, 'Please select a company in the header.');

        $data = $request->validate([
            'date'               => ['required','date'],
            'type'               => ['required', Rule::in($this->types), 'not_in:0'],
            'category_id'        => [
                'nullable',
                Rule::exists('categories','id')->where(fn($q)=>$q
                    ->where('company_id', (int)session('active_company_id'))
                    ->whereIn('type', ['Income/Expense'])
                ),
            ],
            'account_id'         => [
                'required', 'integer', 'not_in:0',
                Rule::exists('categories','id')->where(fn($q)=>$q
                    ->where('company_id', (int)session('active_company_id'))
                    ->where('type','Account')
                ),
            ],
            'amount'             => ['required','numeric','min:0.01'],
            'description'        => ['nullable','string','max:65535'],
            'two_way'            => ['nullable','boolean'],
        ]);
        if ($request->transaction_type === 'tds' && $request->invoice_amount && $request->tds_rate) {
    
            $invoice = (float) $request->invoice_amount;
            $tdsRate = (float) $request->tds_rate;

            $tdsAmount = $invoice * $tdsRate / 100;

            $request->merge([
                'amount' => round($invoice - $tdsAmount, 2),
            ]);
        }
        // Account must be an Account category of current company 
        $account = Category::where('company_id',(int)$cid)
                    ->where('type','Account')
                    ->where('id',$data['account_id'])
                    ->firstOrFail();

        $twoWay = (bool) $request->boolean('two_way');
        $categoryId = $request->category_id ?: null;
        if ($twoWay) {
            $request->validate([
                'counter_account_id' => ['required','different:account_id','exists:categories,id'],
            ]);
            $categoryId = Category::firstOrCreate([
                'company_id' => (int)$cid, // set from context
                'name' => 'Transfer',
                'type' => 'Income/Expense',
            ], [])->id;
        } else {
            $request->validate([
                'category_id' => ['required','integer','exists:categories,id', 'not_in:0'], // must choose one
            ]);
        }
        
        $groupId = (string) Str::uuid();
        // 1) Main entry
         $t1 = Transaction::create([
            'company_id'      => (int)$cid,
            'date'            => $request->date,
            'type'            => $request->type,
            'amount'          => $request->amount,
            'account_id'      => $request->account_id,
            'category_id'     => $categoryId,
            'description'     => $request->description,
            'status'          => $request->status ?: null,
            'name'            => $request->name ?: null,
            'group_id'        => $twoWay ? $groupId : null,
            'main_transaction_id' => null,
            'transaction_type'=> $request->transaction_type,
            'invoice_amount'  => $request->invoice_amount,
            'tds_rate'        => $request->tds_rate,
            'base'            => $data['base'] ?? 0,
            'gst'             => $data['gst'] ?? 0,
            'gstLocked'       => $data['gstLocked'] ?? 0,
            'usable'          => $data['usable'] ?? 0,
            'netRec'          => $data['netRec'] ?? 0,
            'dir'             => $request->type == 'Expense' ? 'out' : 'in',
            'bankfit'         => $request->type == 'Expense' ? -((float)$request->amount) : (float)$request->amount,
            'created_by'      => auth()->user()->id,
        ]);

        // 2) Counter (mirror) entry points to main via main_transaction_id
        if ($twoWay) {
            $flipped = $t1->type === 'Income' ? 'Expense' : 'Income';

            Transaction::create([
                'company_id'          => (int)$cid,
                'date'                => $t1->date,
                'type'                => $flipped,
                'amount'              => $t1->amount,
                'account_id'          => $request->counter_account_id,
                'category_id'         => $categoryId,
                'description'         => trim(($t1->description ?? '')),
                'group_id'            => $groupId,
                'main_transaction_id' => $t1->id,
            ]);
        }

        $monthToRecalc = Carbon::parse($data['date'])->startOfMonth();

        /*OpeningBalanceService::recalcCompanyNextMonthFromMonth(
            (int) ($cid),
            $monthToRecalc,
            (int) ($account->id)
        );

        if ($twoWay) {
            OpeningBalanceService::recalcCompanyNextMonthFromMonth(
                (int) ($cid),
                $monthToRecalc,
                (int) ($request->counter_account_id)
            );
        }*/
        session()->flash('tx_form_date', $data['date']);

        return back()->with('status', 'Transaction saved'.($twoWay ? ' (two-way pair created)' : ''));
    }

    public function edit(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        // Non-admins can only edit within their active company
        if (!$user->is_admin) {
            $cid = (int) session('active_company_id');
            abort_unless($transaction->company_id === $cid, 403);
        }

        $companyId = $transaction->company_id;

        $incomeCats  = Category::where('company_id',$companyId)->where('type','Income')->orderBy('name')->get();
        $expenseCats = Category::where('company_id',$companyId)->where('type','Expense')->orderBy('name')->get();
        $accounts    = Category::where('company_id',$companyId)->where('type','Account')->orderBy('name')->get();
        $types       = ['Income','Expense'];
        $currencySymbol = optional(Company::find($companyId))->currency_symbol ?: '₹';

        return view('admin.transactions.edit', compact(
            'transaction','incomeCats','expenseCats','accounts','types','currencySymbol'
        ));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        if (!$user->is_admin) {
            $cid = (int) session('active_company_id');
            abort_unless($transaction->company_id === $cid, 403);
        }
        $companyId = $transaction->company_id;

        $data = $request->validate([
            'date'               => ['required','date'],
            'type'               => ['required', Rule::in($this->types), 'not_in:0'],
            'category_id'        => [
                'nullable',
                Rule::exists('categories','id')->where(fn($q)=>$q
                    ->where('company_id', $companyId)
                    ->whereIn('type', ['Income/Expense'])
                ),
            ],
            'account_id'         => [
                'required', 'integer', 'not_in:0',
                Rule::exists('categories','id')->where(fn($q)=>$q
                    ->where('company_id', $companyId)
                    ->where('type','Account')
                ),
            ],
            'amount'             => ['nullable','numeric','min:0.01'],
            'description'        => ['nullable','string','max:65535'],
            'counter_account_id' => ['nullable','exists:categories,id'],
            'transaction_type'   => ['nullable','string'],
            'invoice_amount'     => ['nullable','numeric','min:0'],
            'tds_rate'           => ['nullable','numeric'],
            'status'             => ['nullable','string'],
            'name'               => ['nullable','string','max:255'],
            'amount_mode'        => ['nullable','string'],
        ]);

        // TDS: compute amount from invoice
        if ($request->transaction_type === 'tds' && $request->invoice_amount && $request->tds_rate) {
            $invoice   = (float) $request->invoice_amount;
            $tdsRate   = (float) $request->tds_rate;
            $tdsAmount = $invoice * $tdsRate / 100;
            $request->merge(['amount' => round($invoice - $tdsAmount, 2)]);
        }

        // Validate amount now (after possible TDS merge)
        $request->validate(['amount' => ['required','numeric','min:0.01']]);

        $isTransfer = $transaction->isTransfer();
        if ($isTransfer) {
            $request->validate([
                'counter_account_id' => ['required','different:account_id','exists:categories,id'],
            ]);
        }

        $account = Category::where('company_id', $companyId)
            ->where('type','Account')
            ->where('id', $request->account_id)
            ->firstOrFail();

        // GST computed fields
        $txType    = $request->transaction_type;
        $rawAmount = (float) $request->amount;
        $amountMode = $request->amount_mode ?? 'base';
        $gstRate   = 0.18;
        $baseAmount = $txType === 'gst'
            ? ($amountMode === 'base' ? $rawAmount : $rawAmount / (1 + $gstRate))
            : 0;
        $gstAmount  = $txType === 'gst' ? round($baseAmount * $gstRate, 2) : 0;
        $totalAmount = $txType === 'gst' ? round($baseAmount + $gstAmount, 2) : 0;

        DB::transaction(function () use ($request, $transaction, $isTransfer, $companyId, $txType, $baseAmount, $gstAmount, $totalAmount) {
            ['main' => $main, 'counter' => $counter] = $transaction->mainAndCounter();

            $mainData = [
                'date'             => $request->date,
                'type'             => $request->type,
                'amount'           => $request->amount,
                'account_id'       => $request->account_id,
                'description'      => $request->description,
                'status'           => $request->status ?: null,
                'name'             => $request->name ?: null,
                'transaction_type' => $txType,
                'invoice_amount'   => $request->invoice_amount ?: null,
                'tds_rate'         => $request->tds_rate ?: null,
                'amount_mode'      => $request->amount_mode ?? 'base',
                'base'             => $txType === 'gst' ? round($baseAmount, 2) : 0,
                'gst'              => $gstAmount,
                'gstLocked'        => $gstAmount,
                'usable'           => $txType === 'gst' ? round($baseAmount, 2) : (float) $request->amount,
                'netRec'           => $txType === 'gst' ? $totalAmount : (float) $request->amount,
                'dir'              => $request->type === 'Expense' ? 'out' : 'in',
                'bankfit'          => $request->type === 'Expense' ? -((float)$request->amount) : (float)$request->amount,
                'created_by'      => auth()->user()->id,
            ];

            if (!$isTransfer) {
                $mainData['category_id'] = $request->category_id ?: null;
            }
            $main->update($mainData);

            if ($isTransfer) {
                if (!$counter) {
                    $categoryId = Category::firstOrCreate([
                        'company_id' => $companyId,
                        'name'       => 'Transfer',
                        'type'       => 'Income/Expense',
                    ])->id;
                    $main->mirrors()->create([
                        'company_id'          => $companyId,
                        'date'                => $main->date,
                        'type'                => $main->type === 'Income' ? 'Expense' : 'Income',
                        'amount'              => $main->amount,
                        'account_id'          => $request->counter_account_id,
                        'category_id'         => $categoryId,
                        'description'         => trim($main->description ?? ''),
                        'group_id'            => $main->group_id,
                        'main_transaction_id' => $main->id,
                        'created_by'      => auth()->user()->id,
                    ]);
                } else {
                    $counter->update([
                        'date'        => $main->date,
                        'type'        => $main->type === 'Income' ? 'Expense' : 'Income',
                        'amount'      => $main->amount,
                        'account_id'  => $request->counter_account_id,
                        'description' => trim($main->description ?? ''),
                    ]);
                }
            }
        });

        return redirect($request->input('redirect', route('admin.transactions.index')))
            ->with('success', 'Transaction updated' . ($isTransfer ? ' (paired entry synced)' : ''));
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        // Non-admins can only delete within their active company
        if (!$user->is_admin) {
            $cid = (int) session('active_company_id');
            abort_unless($transaction->company_id === $cid, 403);
        }
        
        $isTransfer = $transaction->isTransfer();
        if($isTransfer){
            Transaction::where('main_transaction_id', $transaction->id)->delete();
        }
        $transaction->delete();
        
        /*$monthToRecalc = Carbon::parse($transaction->date)->startOfMonth();
        OpeningBalanceService::recalcCompanyNextMonthFromMonth(
            (int) ($transaction->company_id),
            $monthToRecalc,
            (int) ($transaction->account_id)
        );
        
        if($isTransfer){
            $counters = Transaction::where('main_transaction_id', $transaction->id)->get();
            foreach ($counters as $c) {
                OpeningBalanceService::recalcCompanyNextMonthFromMonth(
                    (int) ($transaction->company_id),
                    $monthToRecalc,
                    (int) ($c->account_id)
                );
            }
        }*/

        return back()->with('status','Transaction deleted');
    }
    public function cashTransfer(Request $request)
    {
        $data = $request->validate([
            'from_company_id' => ['required', 'exists:companies,id'],
            'to_company_id'   => ['required', 'exists:companies,id', 'different:from_company_id'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'date'            => ['required', 'date'],
            'note'            => ['nullable', 'string', 'max:500'],
        ]);

        $desc = 'Cash transfer' . ($data['note'] ? ' — ' . $data['note'] : '');
        $groupId = (string) Str::uuid();

        $fromAccount = Category::where('company_id', $data['from_company_id'])->where('type', 'Account')->where('is_default', true)->first()
            ?? Category::where('company_id', $data['from_company_id'])->where('type', 'Account')->orderBy('name')->firstOrFail();

        $toAccount = Category::where('company_id', $data['to_company_id'])->where('type', 'Account')->where('is_default', true)->first()
            ?? Category::where('company_id', $data['to_company_id'])->where('type', 'Account')->orderBy('name')->firstOrFail();
        
        $fromCat = Category::firstOrCreate(
            ['company_id' => $data['from_company_id'], 'name' => 'Transfer', 'type' => 'Income/Expense']
        );

        $toCat = Category::firstOrCreate(
            ['company_id' => $data['to_company_id'], 'name' => 'Transfer', 'type' => 'Income/Expense']
        );

        $fromCompany = Company::find($data['from_company_id']);
        $toCompany   = Company::find($data['to_company_id']);
       
        $main = Transaction::create([
            'company_id'          => $data['from_company_id'],
            'name'                => $fromCompany->name,
            'date'                => $data['date'],
            'type'                => 'Expense',
            'amount'              => $data['amount'],
            'account_id'          => $fromAccount->id,
            'category_id'         => $fromCat->id,
            'description'         => $desc,
            'group_id'            => $groupId,
            'cashfit'             => -($data['amount']),
            'usable'              => -($data['amount']),
            'netRec'              => $data['amount'],
            'main_transaction_id' => null,
            'dir'                 => 'out',
            'status'              => 'paid',
            'transaction_type'    => 'transfer',
            'created_by'          => auth()->user()->id,
        ]);

        
        Transaction::create([
            'company_id'          => $data['to_company_id'],
            'name'                => $toCompany->name,
            'date'                => $data['date'],
            'type'                => 'Income',
            'amount'              => $data['amount'],
            'account_id'          => $toAccount->id,
            'category_id'         => $toCat->id,
            'description'         => $desc,
            'group_id'            => $groupId,
            'cashfit'             => $data['amount'],
            'usable'              => $data['amount'],
            'netRec'              => $data['amount'],
            'main_transaction_id' => $main->id,
            'dir'                 => 'in',
            'status'              => 'paid',
            'transaction_type'    => 'transfer',
            'created_by'          => auth()->user()->id,
        ]);
       
        $month = Carbon::parse($data['date'])->startOfMonth();
        // OpeningBalanceService::recalcCompanyNextMonthFromMonth((int) $data['from_company_id'], $month, $fromAccount->id);
        // OpeningBalanceService::recalcCompanyNextMonthFromMonth((int) $data['to_company_id'], $month, $toAccount->id);
        return back()->with('status', 'Cash transfer recorded successfully.');
    }
}
