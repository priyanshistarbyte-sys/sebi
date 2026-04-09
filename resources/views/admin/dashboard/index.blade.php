{{-- resources/views/admin/dashboard/index.blade.php --}}
<x-layouts.admin
    title="Dashboard"
    :breadcrumbs="[['label'=>'Dashboard']]"
>
    @if($isAdminAll)
        <div class="mb-4 rounded bg-amber-50 dark:bg-amber-900 dark:bg-opacity-20 border border-amber-200 dark:border-amber-800 p-3 text-amber-800 dark:text-amber-200 transition-colors duration-200">
            You’re viewing <strong>all companies</strong>. Pick a company in the header to see amounts with a fixed currency.
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 md:col-span-1 transition-colors duration-200">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $monthLabel }}</h2>

            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between bg-green-50 dark:bg-green-900 dark:bg-opacity-20 px-3 py-2 rounded text-green-800 dark:text-green-200 transition-colors duration-200">
                    <span>Income</span>
                    <span class="font-semibold">{{ format_money($incomeTotal, $currency) }}</span>
                </div>
                <div class="flex items-center justify-between bg-red-50 dark:bg-red-900 dark:bg-opacity-20 px-3 py-2 rounded text-red-800 dark:text-red-200 transition-colors duration-200">
                    <span>Expense</span>
                    <span class="font-semibold">{{ format_money($expenseTotal, $currency) }}</span>
                </div>
                <div class="flex items-center justify-between bg-emerald-100 dark:bg-emerald-900 dark:bg-opacity-30 px-3 py-2 rounded text-emerald-800 dark:text-emerald-200 transition-colors duration-200">
                    <span>Balance</span>
                    <span class="font-semibold">{{ format_money($balance, $currency) }}</span>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">Cash Flow</h3>
                <canvas id="cashFlowBar" height="160"></canvas>
            </div>
        </div>

        <div class="rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 md:col-span-1 transition-colors duration-200">
            <h3 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">INCOME</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr><th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Category</th><th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse($incomeByCat as $row)
                            <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($row['total'], $currency) }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-2 border border-gray-200 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800" colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 md:col-span-1 transition-colors duration-200">
            <h3 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">EXPENSE</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr><th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Category</th><th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse($expenseByCat as $row)
                            <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($row['total'], $currency) }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-2 border border-gray-200 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800" colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 md:col-span-1 transition-colors duration-200" x-data="{ open:false, html:'', loading:false }">
            <h3 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">AMOUNT LEFT IN ACCOUNT</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr><th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Account</th><th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse($accountsNet as $row)
                            <?php $acc_id = $row['id']; ?>
                            <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">
                                    <button
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200"
                                    @click="
                                        loading=true; open=true; html='';
                                        fetch('{{ route('admin.dashboard.account.transactions', $acc_id) }}', {headers:{'X-Requested-With':'XMLHttpRequest'}})
                                        .then(r=>r.text()).then(t=>{ html=t; loading=false; })
                                        .catch(()=>{ html='<div class=\'p-4\'>Failed to load.</div>'; loading=false; });
                                    ">
                                    {{ format_money($row['total'], $currency) }}
                                </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="p-2 border border-gray-200 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800" colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Modal -->
            <div
                x-show="open"
                x-transition
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/60 p-4 transition-colors duration-200"
                @keydown.escape.window="open=false"
                @click.self="open=false"
            >
                <div class="w-full max-w-3xl rounded-xl bg-white dark:bg-gray-900 shadow dark:shadow-gray-800 p-0 overflow-hidden transition-colors duration-200">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Account Transactions</h2>
                        <button class="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200" @click="open=false">&times;</button>
                    </div>
                    <div class="max-h-[70vh] overflow-auto">
                        <template x-if="loading">
                            <div class="p-6 text-sm text-gray-600 dark:text-gray-400">Loading…</div>
                        </template>
                        <div x-html="html"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div class="grid gap-4 mt-4 md:grid-cols-2">
            <div class="grid gap-4 mt-4 rounded border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900 transition-colors duration-200">
                <div x-data="{ open:false, html:'', loading:false }">
                    <h2 class="mx-2 mt-4 mb-3 text-gray-900 dark:text-gray-100">Full Time Categories</h2>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-x-auto transition-colors duration-200">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr class="text-left">
                                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Category</th>
                                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">Transactions</th>
                                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">All-time Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($categories as $c)
                                @php
                                    $isNegative = $c->total_amount < 0;
                                    $amtClass = $isNegative ? 'text-red-600 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400';
                                    $sym = $currency ?: ($c->company->currency_symbol ?? '₹') ?? '';
                                @endphp
                                <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $c->name }}</td>
                                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">{{ number_format($c->tx_count) }}</td>
                                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">
                                        <button
                                            class="{{ $amtClass }} hover:opacity-80 transition-opacity duration-200"
                                            @click="
                                                loading=true; open=true; html='';
                                                fetch('{{ route('admin.dashboard.category.transactions', $c->id) }}', {headers:{'X-Requested-With':'XMLHttpRequest'}})
                                                .then(r=>r.text()).then(t=>{ html=t; loading=false; })
                                                .catch(()=>{ html='<div class=\'p-4\'>Failed to load.</div>'; loading=false; });
                                            ">
                                            {{ format_money($c->total_amount, $currency) }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-4 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">No dashboard categories.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>


                    <h2 class="mx-2 mt-4 mb-3 text-gray-900 dark:text-gray-100">Monthly Categories</h2>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-x-auto transition-colors duration-200">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr class="text-left">
                                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Category</th>
                                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">Transactions</th>
                                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">Monthly Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($monthlycategories as $c)
                                @php
                                    $isNegative = $c->total_amount < 0;
                                    $amtClass = $isNegative ? 'text-red-600 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400';
                                    $sym = $currency ?: ($c->company->currency_symbol ?? '₹') ?? '';
                                @endphp
                                <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $c->name }}</td>
                                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">{{ number_format($c->tx_count) }}</td>
                                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-end">
                                        <button
                                            class="{{ $amtClass }} hover:opacity-80 transition-opacity duration-200"
                                            @click="
                                                loading=true; open=true; html='';
                                                fetch('{{ route('admin.dashboard.category.transactions', $c->id) }}?month=1', {headers:{'X-Requested-With':'XMLHttpRequest'}})
                                                .then(r=>r.text()).then(t=>{ html=t; loading=false; })
                                                .catch(()=>{ html='<div class=\'p-4\'>Failed to load.</div>'; loading=false; });
                                            ">
                                            {{ format_money($c->total_amount, $currency) }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-4 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">No dashboard categories.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Modal -->
                    <div
                        x-show="open"
                        x-transition
                        x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/60 p-4 transition-colors duration-200"
                        @keydown.escape.window="open=false"
                        @click.self="open=false"
                    >
                        <div class="w-full max-w-3xl rounded-xl bg-white dark:bg-gray-900 shadow dark:shadow-gray-800 p-0 overflow-hidden transition-colors duration-200">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Category Transactions</h2>
                                <button class="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200" @click="open=false">&times;</button>
                            </div>
                            <div class="max-h-[70vh] overflow-auto">
                                <template x-if="loading">
                                    <div class="p-6 text-sm text-gray-600 dark:text-gray-400">Loading…</div>
                                </template>
                                <div x-html="html"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ====== FINANCIAL YEAR TABLE (vertical) ====== --}}
            <div class="overflow-x-auto mt-4 rounded border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900 transition-colors duration-200">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Income & Expenses Overview</h2>
                    <div class="text-sm text-gray-600 dark:text-gray-400">FY: <strong class="text-gray-900 dark:text-gray-100">{{ $fyStartYear }}–{{ substr($fyStartYear+1, -2) }}</strong></div>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 w-40 text-left text-gray-900 dark:text-gray-100">Month</th> {{-- change to "Category" if you prefer --}}
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">Income</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">Expense</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">Cash Flow</th>
                    </tr>
                    </thead>

                    <tbody>
                    @for($i = 0; $i < count($fyLabels); $i++)
                        @php
                        $inc = $fyIncome[$i] ?? 0;
                        $exp = $fyExpense[$i] ?? 0;
                        $cf  = $fyCash[$i] ?? 0;
                        @endphp
                        <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $fyLabels[$i] }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($inc, $currency) }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($exp, $currency) }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right {{ $cf >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ format_money($cf, $currency) }}
                        </td>
                        </tr>
                    @endfor
                    </tbody>

                    <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-left text-gray-900 dark:text-gray-100">Total</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($fyTotals['income'], $currency) }}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($fyTotals['expense'], $currency) }}</th>
                        @php $tcf = $fyTotals['cash']; @endphp
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right {{ $tcf >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ format_money($tcf, $currency) }}
                        </th>
                    </tr>
                    <tr>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-left text-gray-900 dark:text-gray-100">Average</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($fyAverages['income'], $currency) }}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($fyAverages['expense'], $currency) }}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-right">{{ format_money($fyAverages['cash'], $currency) }}</th>
                    </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    {{-- Dashboard: Receivable Widget --}}
    <div x-data="{ open: false }" class="mt-6 rounded-xl border bg-white dark:border-gray-700 p-4 bg-white dark:bg-gray-900 transition-colors duration-200">

        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            class="w-full flex items-center justify-between px-4 py-3 text-left"
        >
            <span class="text-lg font-semibold">Receivables</span>
            <span class="inline-flex items-center gap-2 text-sm text-gray-500">
                FY: <strong>{{ $fyStartYear }}–{{ substr($fyStartYear+1, -2) }}</strong>
                <svg class="h-5 w-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6"/>
                </svg>
            </span>
        </button>

        <div x-show="open" x-transition x-cloak class="border-t px-4 py-4">

            {{-- Top Cards --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">

                <div class="rounded-lg border border-green-100 bg-green-50 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Receivable</div>
                    <div class="text-green-600 text-2xl font-bold mt-1">{{ format_money($totalReceivable, $currency) }}</div>
                    <div class="text-xs text-gray-400 mt-1">all pending income</div>
                </div>

                <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Overdue (&gt; 30 days)</div>
                    <div class="text-red-500 text-2xl font-bold mt-1">{{ format_money($overdue, $currency) }}</div>
                    <div class="text-xs text-gray-400 mt-1">requires follow-up</div>
                </div>

                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Settled This Month</div>
                    <div class="text-blue-600 text-2xl font-bold mt-1">{{ format_money($settled, $currency) }}</div>
                    <div class="text-xs text-gray-400 mt-1">collected this period</div>
                </div>
            </div>

            {{-- Table --}}
             <div class="overflow-x-auto rounded border">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left">
                            <th class="p-2 border">Date</th>
                            <th class="p-2 border">Co.</th>
                            <th class="p-2 border">Party</th>
                            <th class="p-2 border">Type</th>
                            <th class="p-2 border">Base</th>
                            <th class="p-2 border">GST/TDS</th>
                            <th class="p-2 border">Total Due</th>
                            <th class="p-2 border">Days</th>
                            <th class="p-2 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $colspan = 9; @endphp
                        @forelse($receivables as $receivable)
                            @php
                              $total_due  = $receivable->base + $receivable->gstLocked;
                              $days       = (int) \Illuminate\Support\Carbon::parse($receivable->date)->diffInDays(now());
                              $isTransfer = $receivable->main_transaction_id !== null || $receivable->mirrors()->exists();
                              $can_modify = $isTransfer ? ($receivable->main_transaction_id ? false : true) : true;
                              $rAccounts  = $dashAccounts ?? collect();
                              $rCats      = $dashInexCats ?? collect();
                            @endphp
                            <tr>
                                <td class="p-2 border">{{ \Illuminate\Support\Carbon::parse($receivable->date)->toDateString() }}</td>
                                <td class="p-2 border">{{ $receivable->company->name }}</td>
                                <td class="p-2 border">{{ $receivable->name }}</td>
                                <td class="p-2 border">{{ $receivable->transaction_type }}</td>
                                <td class="p-2 border">{{ format_money($receivable->base, $currency) }}</td>
                                <td class="p-2 border">{{ format_money($receivable->gstLocked, $currency) }}</td>
                                <td class="p-2 border">{{ format_money($total_due, $currency) }}</td>
                                <td class="p-2 border">{{ $days }}</td>
                                <td class="p-2 border">
                                    <div class="flex items-center gap-2">
                                        {{-- <form action="{{ route('admin.dashboard.transactions.mark-received', $receivable) }}" method="POST"
                                            onsubmit="return confirm('Mark as received?')">
                                            @csrf @method('PATCH')
                                            <button style="border:1px solid #22c55e;color:#16a34a;background:transparent;" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium leading-none">
                                                <svg style="width:10px;height:10px;flex-shrink:0;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                                                Received
                                            </button>
                                        </form> --}}
                                        @if($can_modify)
                                            <button type="button"
                                                @click="
                                                    Object.assign(editReceivable, {
                                                        action: '{{ route('admin.transactions.update', $receivable) }}',
                                                        transactionType: '{{ $receivable->transaction_type ?? 'gst' }}',
                                                        type: '{{ $receivable->type }}',
                                                        date: '{{ $receivable->date }}',
                                                        amount: '{{ number_format($receivable->amount, 2, '.', '') }}',
                                                        invoice_amount: '{{ $receivable->invoice_amount ?? '' }}',
                                                        tds_rate: '{{ $receivable->tds_rate ?? '1' }}',
                                                        amountMode: 'base',
                                                        account_id: {{ (int)$receivable->account_id }},
                                                        category_id: {{ (int)$receivable->category_id }},
                                                        status: '{{ $receivable->status ?? '' }}',
                                                        name: @js($receivable->name ?? ''),
                                                        description: @js(strip_tags($receivable->description ?? '')),
                                                        accounts: @js($rAccounts->map(fn($a)=>['id'=>$a->id,'name'=>$a->name])),
                                                        cats: @js($rCats->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])),
                                                    });
                                                    editReceivableModal = true;
                                                ">
                                                <svg viewBox="0 0 48 48" class="h-4 w-4"><defs><style>.cls-1{fill:#fc6}.cls-7{fill:#ffba55}</style></defs><g id="pencil"><path class="cls-1" d="M40.58 15.75 12.81 43.53l-1.56-2.59c-.65-1.08-.07-.74-2.61-1.58-.85-2.54-.49-2-1.59-2.61L4.47 35.2 32.25 7.42z"/><path d="M39.58 14.75C18.81 35.52 19 36 15.24 37a10.35 10.35 0 0 1-9.77-2.8L32.25 7.42z" style="fill:#ffde76"/><path d="m12.81 43.53-6 1.75a8.76 8.76 0 0 0-4.12-4.12c.68-2.3.28-.93 1.75-6l3.47 2.08.7 2.08 2.08.7c1.31 2.21.85 1.41 2.12 3.51z" style="fill:#f6ccaf"/><path d="M11.75 41.78c-4.49.81-7.52-1.83-8.52-2.35l1.24-4.23 3.47 2.08.7 2.08 2.08.7z" style="fill:#ffdec7"/><path d="M6.84 45.28c0 .1.09 0-5.84 1.72.81-2.76.42-1.45 1.72-5.84a8.85 8.85 0 0 1 4.12 4.12z" style="fill:#374f68"/><path d="m5.78 43.6-4.14 1.21 1.08-3.65a8.67 8.67 0 0 1 3.06 2.44z" style="fill:#425b72"/><path class="cls-7" d="M38.51 13.68 11.25 40.94c-.64-1.07-.26-.79-1.58-1.24L37.1 12.27zM35.74 10.91 8.3 38.34c-.45-1.33-.17-1-1.25-1.59L34.32 9.49z"/><path class="cls-1" d="M35.74 10.91 9.83 36.81a10.59 10.59 0 0 1-2-.84L34.32 9.49z"/><path d="M46.14 10.2 43.36 13 35 4.64l2.8-2.78a3 3 0 0 1 4.17 0L46.14 6a3 3 0 0 1 0 4.2z" style="fill:#db5669"/><path d="M46.83 7.11c-.77 2.2-4.18 3.15-6.25 1.08L36 3.64l1.8-1.78a3 3 0 0 1 4.17 0c4.61 4.61 4.58 4.45 4.86 5.25z" style="fill:#f26674"/><path d="m43.36 13-2.78 2.78-8.33-8.36L35 4.64z" style="fill:#dad7e5"/><path d="M42.36 12a2.52 2.52 0 0 1-3.56 0l-5.55-5.58L35 4.64z" style="fill:#edebf2"/><path class="cls-1" d="M38.51 13.68 15.24 37a10.69 10.69 0 0 1-3.09.27l25-24.95z"/></g></svg>
                                            </button>
                                            <form action="{{ route('admin.transactions.destroy', $receivable) }}" method="POST"
                                                onsubmit="return confirm('Delete this transaction?')">
                                                @csrf @method('DELETE')
                                                <button>
                                                    <svg x="0" y="0" viewBox="0 0 256 256" class="h-5 w-5" style="enable-background:new 0 0 256 256" xml:space="preserve"><style>.st11{fill:#6770e6}.st12{fill:#5861c7}.st16{fill:#858eff}</style><path class="st11" d="M197 70H59c-8.837 0-16 7.163-16 16v14h170V86c0-8.837-7.163-16-16-16z"/><path class="st16" d="M197 70H59c-8.837 0-16 7.164-16 16v6c0-8.836 7.163-16 16-16h138c8.837 0 16 7.164 16 16v-6c0-8.836-7.163-16-16-16z"/><path class="st12" d="M169 70h-12v-4c0-5.514-4.486-10-10-10h-38c-5.514 0-10 4.486-10 10v4H87v-4c0-12.131 9.869-22 22-22h38c12.131 0 22 9.869 22 22v4z"/><path class="st11" d="M147 44h-38c-12.131 0-22 9.869-22 22v4h.095C88.109 58.803 97.544 50 109 50h38c11.456 0 20.891 8.803 21.905 20H169v-4c0-12.131-9.869-22-22-22z"/><path class="st16" d="M215 116H41a8 8 0 0 1 0-16h174a8 8 0 0 1 0 16z"/><path class="st11" d="M213 116H43l18.038 126.263A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737L213 116z"/><path class="st12" d="M179.944 250H76.056c-7.23 0-13.464-4.682-15.527-11.303l.509 3.565A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737l.509-3.565c-2.063 6.62-8.297 11.302-15.528 11.302zM82.665 136h-.93c-4.141 0-7.377 3.576-6.965 7.697l8.6 86A7 7 0 0 0 90.335 236h.93c4.141 0 7.377-3.576 6.965-7.697l-8.6-86A7 7 0 0 0 82.665 136zM165.165 236h-.93c-4.141 0-7.377-3.576-6.965-7.697l8.6-86a7 7 0 0 1 6.965-6.303h.93c4.141 0 7.377 3.576 6.965 7.697l-8.6 86a7 7 0 0 1-6.965 6.303zM128.5 136h-1a7 7 0 0 0-7 7v86a7 7 0 0 0 7 7h1a7 7 0 0 0 7-7v-86a7 7 0 0 0-7-7z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $colspan }}" class="p-4 text-center text-gray-500">No receivables found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
             </div>

        </div>
    </div>

    {{-- Dashboard: Payables Widget --}}
    <div x-data="{ open: false }" class="mt-6 rounded-xl border bg-white dark:border-gray-700 p-4 bg-white dark:bg-gray-900 transition-colors duration-200">

        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            class="w-full flex items-center justify-between px-4 py-3 text-left"
        >
            <span class="text-lg font-semibold">Payables</span>
            <span class="inline-flex items-center gap-2 text-sm text-gray-500">
                FY: <strong>{{ $fyStartYear }}–{{ substr($fyStartYear+1, -2) }}</strong>
                <svg class="h-5 w-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6"/>
                </svg>
            </span>
        </button>

        <div x-show="open" x-transition x-cloak class="border-t px-4 py-4">

            {{-- Top Cards --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
                
                <div class="rounded-lg border border-green-100 bg-green-50 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Payable</div>
                    <div class="text-green-600 text-2xl font-bold mt-1">{{ format_money($totalPayable, $currency) }}</div>
                    <div class="text-xs text-gray-400 mt-1">all pending expenses</div>
                </div>

                <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Overdue (&gt; 30 days)</div>
                    <div class="text-red-500 text-2xl font-bold mt-1">{{ format_money($overduePayable, $currency) }}</div>
                    <div class="text-xs text-gray-400 mt-1">requires follow-up</div>
                </div>

                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Paid This Month</div>
                    <div class="text-blue-600 text-2xl font-bold mt-1">{{ format_money($paidThisMonth, $currency) }}</div>
                    <div class="text-xs text-gray-400 mt-1">settled this period</div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto rounded border">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left">
                            <th class="p-2 border">Date</th>
                            <th class="p-2 border">Co.</th>
                            <th class="p-2 border">Party</th>
                            <th class="p-2 border">Type</th>
                            <th class="p-2 border">Base</th>
                            <th class="p-2 border">GST/TDS</th>
                            <th class="p-2 border">Total Due</th>
                            <th class="p-2 border">Days</th>
                            <th class="p-2 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $payColspan = 9; @endphp
                        @forelse($payables as $payable)
                            @php
                              $pay_total_due  = $payable->base + $payable->gstLocked;
                              $pay_days       = (int) \Illuminate\Support\Carbon::parse($payable->date)->diffInDays(now());
                              $pay_isTransfer = $payable->main_transaction_id !== null || $payable->mirrors()->exists();
                              $pay_can_modify = $pay_isTransfer ? ($payable->main_transaction_id ? false : true) : true;
                              $pAccounts      = $dashAccounts ?? collect();
                              $pCats          = $dashInexCats ?? collect();
                            @endphp
                            <tr>
                                <td class="p-2 border">{{ \Illuminate\Support\Carbon::parse($payable->date)->toDateString() }}</td>
                                <td class="p-2 border">{{ $payable->company->name }}</td>
                                <td class="p-2 border">{{ $payable->name }}</td>
                                <td class="p-2 border">{{ $payable->transaction_type }}</td>
                                <td class="p-2 border">{{ format_money($payable->base, $currency) }}</td>
                                <td class="p-2 border">{{ format_money($payable->gstLocked, $currency) }}</td>
                                <td class="p-2 border">{{ format_money($pay_total_due, $currency) }}</td>
                                <td class="p-2 border">{{ $pay_days }}</td>
                                <td class="p-2 border">
                                    <div class="flex items-center gap-2">
                                        {{-- <form action="{{ route('admin.dashboard.transactions.mark-paid', $payable) }}" method="POST"
                                            onsubmit="return confirm('Mark as paid?')">
                                            @csrf @method('PATCH')
                                            <button style="border:1px solid #22c55e;color:#16a34a;background:transparent;" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium leading-none">
                                                <svg style="width:10px;height:10px;flex-shrink:0;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                                                Paid
                                            </button>
                                        </form> --}}
                                        @if($pay_can_modify)
                                            <button type="button"
                                                @click="
                                                    Object.assign(editReceivable, {
                                                        action: '{{ route('admin.transactions.update', $payable) }}',
                                                        transactionType: '{{ $payable->transaction_type ?? 'gst' }}',
                                                        type: '{{ $payable->type }}',
                                                        date: '{{ $payable->date }}',
                                                        amount: '{{ number_format($payable->amount, 2, '.', '') }}',
                                                        invoice_amount: '{{ $payable->invoice_amount ?? '' }}',
                                                        tds_rate: '{{ $payable->tds_rate ?? '1' }}',
                                                        amountMode: 'base',
                                                        account_id: {{ (int)$payable->account_id }},
                                                        category_id: {{ (int)$payable->category_id }},
                                                        status: '{{ $payable->status ?? '' }}',
                                                        name: @js($payable->name ?? ''),
                                                        description: @js(strip_tags($payable->description ?? '')),
                                                        accounts: @js($pAccounts->map(fn($a)=>['id'=>$a->id,'name'=>$a->name])),
                                                        cats: @js($pCats->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])),
                                                    });
                                                    editReceivableModal = true;
                                                ">
                                                <svg viewBox="0 0 48 48" class="h-4 w-4"><defs><style>.cls-1{fill:#fc6}.cls-7{fill:#ffba55}</style></defs><g id="pencil"><path class="cls-1" d="M40.58 15.75 12.81 43.53l-1.56-2.59c-.65-1.08-.07-.74-2.61-1.58-.85-2.54-.49-2-1.59-2.61L4.47 35.2 32.25 7.42z"/><path d="M39.58 14.75C18.81 35.52 19 36 15.24 37a10.35 10.35 0 0 1-9.77-2.8L32.25 7.42z" style="fill:#ffde76"/><path d="m12.81 43.53-6 1.75a8.76 8.76 0 0 0-4.12-4.12c.68-2.3.28-.93 1.75-6l3.47 2.08.7 2.08 2.08.7c1.31 2.21.85 1.41 2.12 3.51z" style="fill:#f6ccaf"/><path d="M11.75 41.78c-4.49.81-7.52-1.83-8.52-2.35l1.24-4.23 3.47 2.08.7 2.08 2.08.7z" style="fill:#ffdec7"/><path d="M6.84 45.28c0 .1.09 0-5.84 1.72.81-2.76.42-1.45 1.72-5.84a8.85 8.85 0 0 1 4.12 4.12z" style="fill:#374f68"/><path d="m5.78 43.6-4.14 1.21 1.08-3.65a8.67 8.67 0 0 1 3.06 2.44z" style="fill:#425b72"/><path class="cls-7" d="M38.51 13.68 11.25 40.94c-.64-1.07-.26-.79-1.58-1.24L37.1 12.27zM35.74 10.91 8.3 38.34c-.45-1.33-.17-1-1.25-1.59L34.32 9.49z"/><path class="cls-1" d="M35.74 10.91 9.83 36.81a10.59 10.59 0 0 1-2-.84L34.32 9.49z"/><path d="M46.14 10.2 43.36 13 35 4.64l2.8-2.78a3 3 0 0 1 4.17 0L46.14 6a3 3 0 0 1 0 4.2z" style="fill:#db5669"/><path d="M46.83 7.11c-.77 2.2-4.18 3.15-6.25 1.08L36 3.64l1.8-1.78a3 3 0 0 1 4.17 0c4.61 4.61 4.58 4.45 4.86 5.25z" style="fill:#f26674"/><path d="m43.36 13-2.78 2.78-8.33-8.36L35 4.64z" style="fill:#dad7e5"/><path d="M42.36 12a2.52 2.52 0 0 1-3.56 0l-5.55-5.58L35 4.64z" style="fill:#edebf2"/><path class="cls-1" d="M38.51 13.68 15.24 37a10.69 10.69 0 0 1-3.09.27l25-24.95z"/></g></svg>
                                            </button>
                                            <form action="{{ route('admin.transactions.destroy', $payable) }}" method="POST"
                                                onsubmit="return confirm('Delete this transaction?')">
                                                @csrf @method('DELETE')
                                                <button>
                                                    <svg x="0" y="0" viewBox="0 0 256 256" class="h-5 w-5" style="enable-background:new 0 0 256 256" xml:space="preserve"><style>.st11{fill:#6770e6}.st12{fill:#5861c7}.st16{fill:#858eff}</style><path class="st11" d="M197 70H59c-8.837 0-16 7.163-16 16v14h170V86c0-8.837-7.163-16-16-16z"/><path class="st16" d="M197 70H59c-8.837 0-16 7.164-16 16v6c0-8.836 7.163-16 16-16h138c8.837 0 16 7.164 16 16v-6c0-8.836-7.163-16-16-16z"/><path class="st12" d="M169 70h-12v-4c0-5.514-4.486-10-10-10h-38c-5.514 0-10 4.486-10 10v4H87v-4c0-12.131 9.869-22 22-22h38c12.131 0 22 9.869 22 22v4z"/><path class="st11" d="M147 44h-38c-12.131 0-22 9.869-22 22v4h.095C88.109 58.803 97.544 50 109 50h38c11.456 0 20.891 8.803 21.905 20H169v-4c0-12.131-9.869-22-22-22z"/><path class="st16" d="M215 116H41a8 8 0 0 1 0-16h174a8 8 0 0 1 0 16z"/><path class="st11" d="M213 116H43l18.038 126.263A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737L213 116z"/><path class="st12" d="M179.944 250H76.056c-7.23 0-13.464-4.682-15.527-11.303l.509 3.565A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737l.509-3.565c-2.063 6.62-8.297 11.302-15.528 11.302zM82.665 136h-.93c-4.141 0-7.377 3.576-6.965 7.697l8.6 86A7 7 0 0 0 90.335 236h.93c4.141 0 7.377-3.576 6.965-7.697l-8.6-86A7 7 0 0 0 82.665 136zM165.165 236h-.93c-4.141 0-7.377-3.576-6.965-7.697l8.6-86a7 7 0 0 1 6.965-6.303h.93c4.141 0 7.377 3.576 6.965 7.697l-8.6 86a7 7 0 0 1-6.965 6.303zM128.5 136h-1a7 7 0 0 0-7 7v86a7 7 0 0 0 7 7h1a7 7 0 0 0 7-7v-86a7 7 0 0 0-7-7z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $payColspan }}" class="p-4 text-center text-gray-500">No payables found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Dashboard: GST/TDS Widget --}}
    <div x-data="{ open: false }" class="mt-6 rounded-xl border bg-white dark:border-gray-700 p-4 bg-white dark:bg-gray-900 transition-colors duration-200">

        <button type="button" @click="open = !open" :aria-expanded="open"
            class="w-full flex items-center justify-between px-4 py-3 text-left">
            <span class="text-lg font-semibold">GST / TDS</span>
            <span class="inline-flex items-center gap-2 text-sm text-gray-500">
                FY: <strong>{{ $fyStartYear }}–{{ substr($fyStartYear+1, -2) }}</strong>
                <svg class="h-5 w-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6"/>
                </svg>
            </span>
        </button>

        <div x-show="open" x-transition x-cloak class="border-t px-4 py-4">

            {{-- Month filter --}}
            <form method="GET" action="" class="mb-4">
                <select name="gst_month" onchange="this.form.submit()"
                    class="border rounded px-3 py-1.5 text-sm">
                    <option value="all" {{ $gstFilter === 'all' ? 'selected' : '' }}>All / Current</option>
                    @foreach($gstMonths as $ym)
                        <option value="{{ $ym }}" {{ $gstFilter === $ym ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('F Y') }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- Summary Cards --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin-bottom:1.5rem;">
                <div class="rounded-lg border-t-4 border-blue-500 bg-white dark:bg-gray-800 shadow-sm p-3">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">GST Collected</div>
                    <div class="text-blue-600 dark:text-blue-400 text-xl font-bold mt-1">{{ format_money($gstCollected, $currency) }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">from income</div>
                </div>
                <div class="rounded-lg border-t-4 border-red-400 bg-white dark:bg-gray-800 shadow-sm p-3">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">GST Paid (ITC)</div>
                    <div class="text-red-500 dark:text-red-400 text-xl font-bold mt-1">{{ format_money($gstPaid, $currency) }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">from expenses</div>
                </div>
                <div class="rounded-lg border-t-4 border-yellow-400 bg-white dark:bg-gray-800 shadow-sm p-3">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Net GST Locked</div>
                    <div class="text-yellow-600 dark:text-yellow-400 text-xl font-bold mt-1">{{ format_money($netGst, $currency) }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">owed to govt</div>
                </div>
                <div class="rounded-lg border-t-4 border-purple-400 bg-white dark:bg-gray-800 shadow-sm p-3">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">TDS Held (Pure TDS)</div>
                    <div class="text-purple-600 dark:text-purple-400 text-xl font-bold mt-1">{{ format_money($tdsHeldPure, $currency) }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">standalone TDS</div>
                </div>
                <div class="rounded-lg border-t-4 border-cyan-400 bg-white dark:bg-gray-800 shadow-sm p-3">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">TDS Held (on GST Invoices)</div>
                    <div class="text-cyan-600 dark:text-cyan-400 text-xl font-bold mt-1">{{ format_money($tdsOnGst, $currency) }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">combined GST+TDS</div>
                </div>
                <div class="rounded-lg border-t-4 border-green-400 bg-white dark:bg-gray-800 shadow-sm p-3">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total TDS Refundable</div>
                    <div class="text-green-600 dark:text-green-400 text-xl font-bold mt-1">{{ format_money($totalTdsRefundable, $currency) }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">claim at filing</div>
                </div>
            </div>

            {{-- Transactions side by side --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                {{-- GST Transactions --}}
                <div class="overflow-x-auto rounded border">
                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-900 dark:text-gray-100">GST Transactions</div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="p-2 border">Date</th>
                                <th class="p-2 border">Co.</th>
                                <th class="p-2 border">Party</th>
                                <th class="p-2 border">Dir</th>
                                <th class="p-2 border">Base</th>
                                <th class="p-2 border">GST</th>
                                <th class="p-2 border">TDS</th>
                                <th class="p-2 border">Bank Hit</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @php $gstColspan = 8; @endphp
                            @forelse($gstTransactions as $gt)
                                <tr>
                                    <td class="p-2 border">{{ \Carbon\Carbon::parse($gt->date)->format('d M Y') }}</td>
                                    <td class="p-2 border">{{ $gt->company->name ?? '-' }}</td>
                                    <td class="p-2 border">{{ $gt->name }}</td>
                                    <td class="p-2 border">
                                        @if($gt->type === 'Income')
                                            <span style="border:1px solid #22c55e;color:#16a34a;" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium">▲ IN</span>
                                        @else
                                            <span style="border:1px solid #ef4444;color:#dc2626;" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium">▼ OUT</span>
                                        @endif
                                    </td>
                                    <td class="p-2 border">{{ format_money($gt->base, $currency) }}</td>
                                    <td class="p-2 border">{{ format_money($gt->gstLocked, $currency) }}</td>
                                    <td class="p-2 border">{{ format_money($gt->tds, $currency) }}</td>
                                    <td class="p-2 border">{{ format_money($gt->gstLocked, $currency) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $gstColspan }}" class="p-4 text-center text-gray-500">No GST transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TDS Transactions --}}
                <div class="overflow-x-auto rounded border">
                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-900 dark:text-gray-100">TDS Transactions</div>
                     <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="p-2 border">Date</th>
                                <th class="p-2 border">Co.</th>
                                <th class="p-2 border">Party</th>
                                <th class="p-2 border">Invoice</th>
                                <th class="p-2 border">TDS%</th>
                                <th class="p-2 border">TDS Held</th>
                                <th class="p-2 border">Bank Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tdsTransactions as $td)
                                <tr>
                                    <td class="p-2 border">{{ \Carbon\Carbon::parse($td->date)->format('d M Y') }}</td>
                                    <td class="p-2 border">{{ $td->company->name ?? '-' }}</td>
                                    <td class="p-2 border">{{ $td->name }}</td>
                                    <td class="p-2 border">{{ format_money($td->invoice_amount, $currency) }}</td>
                                    <td class="p-2 border">{{ $td->tds_rate }}%</td>
                                    <td class="p-2 border">{{ format_money($td->tds, $currency) }}</td>
                                    <td class="p-2 border">{{ format_money($td->amount, $currency) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-4 text-center text-gray-500">No TDS transactions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- Dashboard: Files Widget --}}
    <div class="mt-6">
        <div x-data="fileWidget()" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 transition-colors duration-200">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Files</h2>
                @if(session('status')) <div class="text-sm text-green-700 dark:text-green-400 transition-colors duration-200">{{ session('status') }}</div> @endif
            </div>

            {{-- Upload --}}
            <form method="POST" action="{{ route('admin.dashboard.files.store') }}" enctype="multipart/form-data"
                class="flex flex-wrap items-center gap-3 mb-4">
                @csrf
                <input type="file" name="files[]" multiple class="border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                <button class="px-3 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">Upload</button>
                @error('files.*') <div class="text-red-600 dark:text-red-400 text-sm transition-colors duration-200">{{ $message }}</div> @enderror
            </form>

            {{-- Recent files (top 10) --}}
            <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="text-left">
                            <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Name</th>
                            <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Type</th>
                            <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Size</th>
                            <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Uploaded</th>
                            <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $f)
                            <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="underline text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200"
                                                @click="openPreview('{{ route('admin.dashboard.files.preview', $f) }}')">
                                            {{ $f->name }}
                                        </button>
                                        <button type="button" class="text-xs text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200"
                                                @click="startRename({{ $f->id }}, @js($f->name), '{{ route('admin.dashboard.files.update', $f) }}')">rename</button>
                                    </div>
                                    <div x-show="renamingId === {{ $f->id }}" x-cloak class="mt-2 flex gap-2">
                                        <input type="text" x-model="renameVal" class="border border-gray-300 dark:border-gray-600 rounded p-1 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                        <button class="px-2 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200" @click="saveRename()">Save</button>
                                        <button class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" @click="cancelRename()">Cancel</button>
                                    </div>
                                </td>
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                    @if($f->is_image) Image @elseif($f->is_pdf) PDF @else {{ $f->ext ?: 'file' }} @endif
                                </td>
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ number_format($f->size/1024, 1) }} KB</td>
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $f->created_at->format('Y-m-d H:i') }}</td>
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                    <a href="{{ Storage::disk('public')->url($f->path) }}" target="_blank" class="underline text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200">Download</a>

                                    <form action="{{ route('admin.dashboard.files.destroy', $f) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this file?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 dark:text-red-400 underline hover:text-red-800 dark:hover:text-red-300 transition-colors duration-200 ml-2">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">No files yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Modal --}}
            <div x-show="open" x-transition x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/60 p-4 transition-colors duration-200"
                @keydown.escape.window="open=false" @click.self="open=false">
                <div class="w-full max-w-4xl rounded-xl bg-white dark:bg-gray-900 shadow dark:shadow-gray-800 overflow-hidden transition-colors duration-200">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Preview</h2>
                        <button class="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200" @click="open=false">&times;</button>
                    </div>
                    <div class="max-h-[80vh] overflow-auto">
                        <template x-if="loading">
                            <div class="p-6 text-sm text-gray-600 dark:text-gray-400">Loading…</div>
                        </template>
                        <div x-html="html"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine component --}}
    <script>
        function fileWidget(){
            return {
                open:false, loading:false, html:'',
                renamingId:null, renameVal:'', renameUrl:'',
                openPreview(url){
                    this.open = true; this.loading = true; this.html = '';
                    fetch(url, {headers: {'X-Requested-With':'XMLHttpRequest'}})
                    .then(r => r.text()).then(t => { this.html = t; this.loading = false; })
                    .catch(() => { this.html = '<div class="p-4">Failed to load.</div>'; this.loading=false; });
                },
                startRename(id, current, url){
                    this.renamingId = id; this.renameVal = current; this.renameUrl = url;
                },
                cancelRename(){
                    this.renamingId = null; this.renameVal = ''; this.renameUrl = '';
                },
                saveRename(){
                    fetch(this.renameUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'X-Requested-With':'XMLHttpRequest',
                        },
                        body: JSON.stringify({ name: this.renameVal })
                    }).then(r => {
                        if (!r.ok) throw new Error('Failed');
                        window.location.reload();
                    }).catch(() => alert('Rename failed'));
                }
            }
        }
    </script>


    {{-- Chart.js (via CDN) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data from PHP
        const cashFlow    = @json($cashFlow);

        // Cash flow bar
        new Chart(document.getElementById('cashFlowBar'), {
            type: 'bar',
            data: {
                labels: ['Income', 'Expense'],
                datasets: [{ 
                    data: [cashFlow.income, cashFlow.expense],
                    backgroundColor: ['#61ce81ff', '#f06161ff'] 
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</x-layouts.admin>
