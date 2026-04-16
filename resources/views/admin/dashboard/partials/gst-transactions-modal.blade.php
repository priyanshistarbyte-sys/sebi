<div class="p-4">
    <div class="mb-3 flex items-center gap-2">
        <div class="text-base font-semibold text-gray-900 dark:text-gray-100">GST Transactions</div>
        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('admin.dashboard.gst.transactions.export') . '?' . http_build_query(['gst_month' => $gstFilter]) }}"
               class="inline-flex items-center rounded bg-emerald-600 px-3 py-1.5 text-white text-xs hover:bg-emerald-700">
                Export XLSX
            </a>
            <a href="{{ route('admin.dashboard.gst.transactions.export') . '?' . http_build_query(['gst_month' => $gstFilter, 'format' => 'csv']) }}"
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
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Dir</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Base</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">GST</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">TDS</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Bank Hit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $gt)
                    <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($gt->date)->format('d M Y') }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $gt->name }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700">
                            @if($gt->type === 'Income')
                                <span style="border:1px solid #22c55e;color:#16a34a;" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium">▲ IN</span>
                            @else
                                <span style="border:1px solid #ef4444;color:#dc2626;" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium">▼ OUT</span>
                            @endif
                        </td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ format_money($gt->base, $currency) }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ format_money($gt->gstLocked, $currency) }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ format_money($gt->tds, $currency) }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ format_money($gt->gstLocked, $currency) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-center text-gray-500 dark:text-gray-400">No GST transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
