<div class="p-4">
    <div class="mb-3 flex items-center gap-2">
        <div class="text-base font-semibold">
            {{ $category->name }}
            <span class="text-xs text-gray-500">({{ $category->type }})</span>
        </div>

        <div class="ml-auto flex items-center gap-2">
            {{-- Excel --}}
            <a
                href="{{ route('admin.dashboard.category.transactions.export', $category) . '?' . http_build_query(array_merge(request()->query(),['is_account'=>$is_account])) }}"
                class="inline-flex items-center rounded bg-emerald-600 px-3 py-1.5 text-white text-xs hover:bg-emerald-700"
                title="Export to Excel"
            >
                Export XLSX
            </a>

            {{-- CSV (optional) --}}
            <a
                href="{{ route('admin.dashboard.category.transactions.export', $category) . '?' . http_build_query(array_merge(request()->query(),['is_account'=>$is_account], ['format' => 'csv'])) }}"
                class="inline-flex items-center rounded bg-slate-700 px-3 py-1.5 text-white text-xs hover:bg-slate-800"
                title="Export to CSV"
            >
                Export CSV
            </a>
        </div>
    </div>

    <div class="rounded border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left">
                    <th class="p-2 border">Date</th>
                    <th class="p-2 border">Account</th>
                    <th class="p-2 border text-end">Amount</th>
                    <th class="p-2 border">Description</th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $t)
                @php
                    $isExpense = $t->type === 'Expense';
                    $sign = $isExpense ? '-' : '+';
                    $sym  = $t->company?->currency_symbol ?? '₹';
                    $amtClass = $isExpense ? 'text-red-600' : 'text-emerald-700';
                @endphp
                <tr class="odd:bg-white even:bg-gray-50">
                    <td class="p-2 border">{{ \Illuminate\Support\Carbon::parse($t->date)->toDateString() }}</td>
                    <td class="p-2 border">{{ $t->account?->name ?? '—' }}</td>
                    <td class="p-2 border text-end {{ $amtClass }}">{{ format_money($t->amount, $currency) }}</td>
                    <td class="p-2 border">{!! $t->description !!}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-3 text-center text-gray-500 dark:text-gray-400">No transactions.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $transactions->links() }}
    </div>
</div>
