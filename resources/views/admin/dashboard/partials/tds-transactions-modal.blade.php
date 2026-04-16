<div class="p-4">
    <div class="mb-3 flex items-center gap-2">
        <div class="text-base font-semibold text-gray-900 dark:text-gray-100">TDS Transactions</div>
        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('admin.dashboard.tds.transactions.export') . '?' . http_build_query(['gst_month' => $gstFilter]) }}"
               class="inline-flex items-center rounded bg-emerald-600 px-3 py-1.5 text-white text-xs hover:bg-emerald-700">
                Export XLSX
            </a>
            <a href="{{ route('admin.dashboard.tds.transactions.export') . '?' . http_build_query(['gst_month' => $gstFilter, 'format' => 'csv']) }}"
               class="inline-flex items-center rounded bg-slate-700 px-3 py-1.5 text-white text-xs hover:bg-slate-800">
                Export CSV
            </a>
        </div>
    </div>

    <div class="rounded border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="text-left">
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Date</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Party</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Invoice</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">TDS %</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">TDS Held</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Bank Received</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $td)
                    <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($td->date)->format('d M Y') }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $td->name }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ format_money($td->invoice_amount, $currency) }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $td->tds_rate }}%</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ format_money($td->tds, $currency) }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ format_money($td->amount, $currency) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-center text-gray-500 dark:text-gray-400">No TDS transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
