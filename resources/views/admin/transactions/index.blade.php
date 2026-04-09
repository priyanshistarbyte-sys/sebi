<x-layouts.admin title="Transactions" :breadcrumbs="[
    ['label'=>'Dashboard','url'=>route('admin.dashboard')],
    ['label'=>'Transactions']
]">
    <style>
        .pagination-custom span[aria-current="page"] span{
            background: #2563eb;
            color: white;
        }
    </style>
    {{-- Banner for admin on "All companies" --}}
    @if($companyIsAll)
        <div class="mb-4 rounded bg-green-100 dark:bg-green-900 dark:bg-opacity-20 border border-green-200 dark:border-green-800 p-3 text-green-800 dark:text-green-200 transition-colors duration-200">
            Viewing all companies — select a company in the header to add transactions.
        </div>
    @endif

    {{-- FORM --}}
    <div class="mb-2">
        {{-- ACCORDION: Add Transaction --}}
        <div
            x-data="{ open: {{ ($canCreate || $errors->any()) ? 'true' : 'false' }} }"
            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 transition-colors duration-200"
        >
            <button
                type="button"
                @click="open = !open"
                :aria-expanded="open"
                class="w-full flex items-center justify-between px-4 py-3 text-left"
                {{ $canCreate ? '' : 'disabled' }}
            >
                <span class="text-lg font-semibold">Add Transaction</span>

                <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                    @if(!$canCreate)
                        <em class="text-gray-500">Select a company to enable</em>
                    @endif
                    <svg
                        class="h-5 w-5 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 9l6 6 6-6"/>
                    </svg>
                </span>
            </button>

            <div x-show="open" x-transition x-cloak class="border-t px-4 py-4">
                <form method="POST" action="{{ route('admin.transactions.store') }}"
                    {{ $canCreate ? '' : 'aria-disabled=true' }}>
                    @csrf
                    <div
                        x-data="{
                             type: '{{ old('type','0') }}',
                            transactionType: '{{ old('transaction_type','gst') }}',
                            tds_rate: '{{ old('tds_rate','1') }}',
                            invoiceAmount: '{{ old('invoice_amount','') }}',
                            category_id: {{ (int) old('category_id', 0) }},
                            twoWay: {{ old('two_way') ? 'true' : 'false' }},
                            amountMode: '{{ old('amount_mode','base') }}',
                            rawAmount: '{{ old('amount','') }}',
                            name: '{{ old('name','') }}',
                            inex: @js($inexCats->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])),
                            accountsList: @js($accounts->map(fn($a)=>['id'=>$a->id,'name'=>$a->name])),
                            get cats(){ return this.inex },
                            get gstRate(){ return 0.18 },
                            get baseAmount(){
                                let v = parseFloat(this.rawAmount) || 0;
                                return this.amountMode === 'base' ? v : v / (1 + this.gstRate);
                            },
                            get gstAmount(){ return this.baseAmount * this.gstRate },
                            get totalAmount(){ return this.baseAmount + this.gstAmount },
                            get tdsRateNum(){ return parseFloat(this.tds_rate) || 0 },
                            get tdsAmount(){ return (parseFloat(this.invoiceAmount) || 0) * this.tdsRateNum / 100 },
                            get tdsUsable(){ return (parseFloat(this.invoiceAmount) || 0) - this.tdsAmount },
                            fmt(n){ return '₹' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',') }
                        }"
                    >
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Transaction Type</label>
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $txTypes = [
                                            'gst'    => ['icon' => '🧾', 'title' => 'GST',        'sub' => '18% tax'],
                                            'tds'    => ['icon' => '🏛️', 'title' => 'TDS',        'sub' => 'govt deduct'],
                                            'exempt' => ['icon' => '🏦', 'title' => 'Tax Exempt', 'sub' => 'bank, no tax'],
                                            'cash'   => ['icon' => '💵', 'title' => 'Cash',       'sub' => 'physical cash'],
                                        ];
                                    @endphp
                                    @foreach($txTypes as $val => $info)
                                        <label class="cursor-pointer" @click="transactionType = '{{ $val }}'">
                                            <input type="radio" name="transaction_type" value="{{ $val }}"
                                                x-model="transactionType"
                                                class="sr-only"
                                                {{ old('transaction_type', 'gst') === $val ? 'checked' : '' }}
                                                {{ $canCreate ? '' : 'disabled' }}>
                                            <div class="flex flex-col items-center justify-center w-28 h-20 rounded-xl border-2 px-2 py-2 text-center transition-all"
                                                :class="transactionType === '{{ $val }}'
                                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 dark:border-blue-400 shadow-sm'
                                                    : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                <span class="text-xl leading-none mb-1">{{ $info['icon'] }}</span>
                                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $info['title'] }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $info['sub'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('transaction_type')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Date</label>
                                <input type="date" name="date" value="{{ old('date', $defaultDateForForm) }}" class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                @error('date')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Type</label>
                                <select name="type" x-model="type" class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                    <option value="0">Select Type</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('type')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>

                            <div x-show="transactionType === 'gst' || transactionType === 'exempt' || transactionType === 'cash'">
                                <label class="block text-sm font-medium mb-1">Amount</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-2.5 text-gray-500">
                                        {{ $currencySymbol ?: '₹' }}
                                    </span>
                                    <input type="number" step="0.01" name="amount" x-model="rawAmount" value="{{ old('amount') }}"
                                        class="w-full border rounded p-2 pl-7" {{ $canCreate ? '' : 'disabled' }}>
                                </div>
                                @error('amount')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>
                            
                            {{-- GST Breakdown --}}
                            <div x-show="transactionType === 'gst' && parseFloat(rawAmount) > 0" class="sm:col-span-2 lg:col-span-3">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-sm space-y-1 transition-colors duration-200">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Base Amount</span>
                                        <span class="text-gray-900 dark:text-gray-100" x-text="fmt(baseAmount)"></span>
                                    </div>
                                    <div class="flex justify-between text-blue-600 dark:text-blue-400">
                                        <span>GST @ 18%</span>
                                        <span x-text="'+ ' + fmt(gstAmount)"></span>
                                    </div>
                                    <div class="flex justify-between font-semibold border-t border-gray-200 dark:border-gray-600 pt-1 text-gray-900 dark:text-gray-100">
                                        <span>Bank Received</span>
                                        <span x-text="fmt(totalAmount)"></span>
                                    </div>
                                    <div class="flex justify-between text-orange-500 dark:text-orange-400">
                                        <span>GST Locked (owed to govt)</span>
                                        <span x-text="'− ' + fmt(gstAmount)"></span>
                                    </div>
                                    <div class="flex justify-between font-semibold text-green-700 dark:text-green-400">
                                        <span>Usable Amount</span>
                                        <span x-text="fmt(baseAmount)"></span>
                                    </div>
                                </div>
                                <input type="hidden" name="base"      :value="transactionType === 'gst' ? baseAmount.toFixed(2) : ''">
                                <input type="hidden" name="gst"       :value="transactionType === 'gst' ? gstAmount.toFixed(2) : ''">
                                <input type="hidden" name="gstLocked" :value="transactionType === 'gst' ? gstAmount.toFixed(2) : ''">
                                <input type="hidden" name="usable"    :value="transactionType === 'gst' ? baseAmount.toFixed(2) : ''">
                                <input type="hidden" name="netRec"    :value="transactionType === 'gst' ? totalAmount.toFixed(2) : ''">
                            </div>

                            {{-- Two-way checkbox --}}
                            <div class="pt-6">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="two_way" value="1" x-model="twoWay" class="rounded border-gray-300" {{ $canCreate ? '' : 'disabled' }}>
                                    <span class="text-sm font-medium">Two-way transaction (create opposite entry)</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Account</label>
                                <select name="account_id" class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                    <option value="0">Select Account</option>
                                    @foreach($accounts as $a)
                                        <option value="{{ $a->id }}" {{ (int)old('account_id')===$a->id ? 'selected':'' }}>
                                            {{ $a->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>

                            {{-- Category (hidden/disabled when two-way) --}}
                            <div x-show="!twoWay" x-cloak>
                                <label class="block text-sm font-medium mb-1">Category</label>
                                <select name="category_id"
                                        x-model.number="category_id"
                                        :disabled="twoWay"
                                        class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                    <option value="">Select Category</option>
                                    <template x-for="c in cats" :key="c.id">
                                        <option :value="Number(c.id)" x-text="c.name"></option>
                                    </template>
                                </select>
                                @error('category_id')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>

                            {{-- Counter Account (shown when two-way) --}}
                            <div x-show="twoWay" x-cloak>
                                <label class="block text-sm font-medium mb-1">
                                    <span
                                        x-text="type==='Income'
                                            ? 'Expense Account'
                                            : (type==='Expense' ? 'Income Account' : 'Counter Account.')"
                                    ></span>
                                </label>
                                <select name="counter_account_id"
                                        :required="twoWay"
                                        class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                    <option value="0"
                                        x-text="type==='Income'
                                            ? 'Select Expense Account'
                                            : (type==='Expense' ? 'Select Income Account' : 'Select Account.')">
                                    @foreach($accounts as $a)
                                        <option value="{{ $a->id }}" {{ (int)old('account_id')===$a->id ? 'selected':'' }}>
                                            {{ $a->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('counter_account_id')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Payment Status</label>
                                <select name="status" x-model="status" class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                    <option value="0">Select Status</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                </select>
                                @error('status')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>

                            <div x-show="transactionType === 'gst'">
                                <label class="block text-sm font-medium mb-1">Amount Mode</label>
                                <div class="flex rounded border overflow-hidden">
                                    <button type="button"
                                        @click="amountMode = 'base'"
                                        :class="amountMode === 'base' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
                                        class="flex-1 px-3 py-2 text-sm font-medium transition-colors">
                                        Base (excl. GST)
                                    </button>
                                    <button type="button"
                                        @click="amountMode = 'total'"
                                        :class="amountMode === 'total' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
                                        class="flex-1 px-3 py-2 text-sm font-medium border-l transition-colors">
                                        Total (incl. GST)
                                    </button>
                                </div>
                                <input type="hidden" name="amount_mode" :value="amountMode">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Client / Vendor</label>
                                <input type="text" name="name" x-model="name" class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>
                            <div x-show="transactionType === 'tds'">
                                <label class="block text-sm font-medium mb-1">Invoice Amount</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-2.5 text-gray-500">{{ $currencySymbol ?: '₹' }}</span>
                                    <input type="number" step="0.01" name="invoice_amount" x-model="invoiceAmount" value="{{ old('invoice_amount') }}"
                                        class="w-full border rounded p-2 pl-7" {{ $canCreate ? '' : 'disabled' }}>
                                </div>
                                @error('invoice_amount')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>
                            <div x-show="transactionType === 'tds'">
                                <label class="block text-sm font-medium mb-1">TDS RATE</label>
                                 <select name="tds_rate" x-model="tds_rate" class="w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>
                                    <option value="1">1%</option>
                                    <option value="2">2%</option>
                                </select>
                                @error('tds_rate')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>

                            {{-- TDS Breakdown --}}
                            <div x-show="transactionType === 'tds' && parseFloat(invoiceAmount) > 0" class="sm:col-span-2 lg:col-span-3">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-sm space-y-1 transition-colors duration-200">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Invoice Amount</span>
                                        <span class="text-gray-900 dark:text-gray-100" x-text="fmt(parseFloat(invoiceAmount) || 0)"></span>
                                    </div>
                                    <div class="flex justify-between text-red-600 dark:text-red-400">
                                        <span x-text="'TDS @ ' + tds_rate + '% (held by govt)'"></span>
                                        <span x-text="'− ' + fmt(tdsAmount)"></span>
                                    </div>
                                    <div class="flex justify-between font-semibold border-t border-gray-200 dark:border-gray-600 pt-1 text-green-700 dark:text-green-400">
                                        <span>Bank Received (usable)</span>
                                        <span x-text="fmt(tdsUsable)"></span>
                                    </div>
                                </div>
                                <input type="hidden" name="amount" x-bind:disabled="transactionType !== 'tds'" :value="tdsAmount.toFixed(2)">
                            </div>

                        </div>
                    </div>

                    <div class="col-span-12 my-5">
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" class="rich-desc w-full border rounded p-2" {{ $canCreate ? '' : 'disabled' }}>{{ old('description') }}</textarea>
                        @error('description')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <button class="px-4 py-2 bg-blue-600 text-white rounded" {{ $canCreate ? '' : 'disabled' }}>
                            Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- FILTERS + LIST --}}
    {{-- MOBILE: simple card list --}}

    @php
        $activeFilters = 0;
        if($fDateStart || $fDateEnd)   $activeFilters++;
        if($fType)   $activeFilters++;
        if($fAccount)$activeFilters++;
    @endphp
    <div class="md:hidden space-y-3">
        <div class="mb-3">
            <div x-data="{ open: {{ $activeFilters ? 'true' : 'false' }} }" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 transition-colors duration-200">
                <button type="button"
                        @click="open = !open"
                        :aria-expanded="open"
                        class="w-full flex items-center justify-between px-4 py-3 text-left text-gray-900 dark:text-gray-100 transition-colors duration-200">
                    <span class="text-base font-semibold">Filters</span>
                    <span class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                        @if($activeFilters)
                            <span class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-0.5 text-xs transition-colors duration-200">{{ $activeFilters }}</span>
                        @endif
                        <svg class="h-5 w-5 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l6 6 6-6"/>
                        </svg>
                    </span>
                </button>

                <div x-show="open" x-transition x-cloak class="border-t px-4 py-4">
                    <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid gap-3">
                        <div>
                            <label class="sr-only">Start</label>
                            <input type="date"
                                name="start_date"
                                value="{{ old('start_date', $clampedStart ?? $startDate) }}"
                                min="{{ $startDate }}"
                                max="{{ $endDate }}"
                                class="border rounded p-2 text-sm">
                            <label class="sr-only">End</label>
                            <input type="date"
                                name="end_date"
                                value="{{ old('end_date', $clampedEnd ?? $endDate) }}"
                                min="{{ $startDate }}"
                                max="{{ $endDate }}"
                                class="border rounded p-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium mb-1">Type</label>
                            <select name="type" class="w-full border rounded p-2 text-sm">
                                <option value="" {{ $fType ? '' : 'selected' }}>All</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}" {{ $fType===$t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium mb-1">Account</label>
                            <select name="account_id" class="w-full border rounded p-2 text-sm">
                                <option value="0" {{ $fAccount ? '' : 'selected' }}>All</option>
                                @foreach($accountChoices as $acc)
                                    <option value="{{ $acc->id }}" {{ (int)$fAccount===$acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }} @if($isAdmin && $companyIsAll) ({{ $acc->company->name ?? '' }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Search box --}}
                        <input type="search" name="q" placeholder="Search description, category, account, amount" value="{{ request('q','') }}" class="border rounded p-2 text-sm min-w-[16rem]">

                        {{-- Per-page selection --}}
                        <select name="per_page" class="border rounded p-2 text-sm">
                            @php $pp = (int) request('per_page', 15); @endphp
                            <option value="10" {{ $pp===10 ? 'selected' : '' }}>10 / page</option>
                            <option value="15" {{ $pp===15 ? 'selected' : '' }}>15 / page</option>
                            <option value="25" {{ $pp===25 ? 'selected' : '' }}>25 / page</option>
                            <option value="50" {{ $pp===50 ? 'selected' : '' }}>50 / page</option>
                            <option value="100" {{ $pp===100 ? 'selected' : '' }}>100 / page</option>
                            <option value="250" {{ $pp===250 ? 'selected' : '' }}>250 / page</option>
                            <option value="500" {{ $pp===500 ? 'selected' : '' }}>500 / page</option>
                        </select>

                        <div class="flex gap-2 pt-1">
                            <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Apply</button>
                            @if($activeFilters)
                                <a href="{{ route('admin.transactions.index') }}" class="px-3 py-2 border rounded text-sm">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @forelse($transactions as $t)
            @php
            $isExpense = $t->type === 'Expense';
            $amtClass  = $isExpense ? 'text-red-600 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400';
            $divClass  = $isExpense ? 'bg-red-200/25 dark:bg-red-900/20' : 'bg-green-100/25 dark:bg-green-900/20';
            $sign      = $isExpense ? '-' : '+';
            // If you still support admin "All", pick symbol per row; else use $currencySymbol
            $rowSym    = ($isAdmin ?? false) && ($companyIsAll ?? false)
                            ? ($t->company->currency_symbol ?? '')
                            : ($currencySymbol ?? '₹');

            // Per-company category lists for this row (provided by controller)
            $rowCats   = $categoryMap[$t->company_id] ?? ['Income'=>[],'Expense'=>[],'Account'=>[], 'InEx'=>[]];

            $counter            = $t->main_transaction_id ? $t : $t->mirrors()->first();
            $prefillCounterAcc  = $counter?->id === $t->id ? $t->account_id : ($counter?->account_id ?? null);
            $isTransfer         = $t->main_transaction_id !== null || $t->mirrors()->exists();
            $can_modify         = $isTransfer ? ($t->main_transaction_id ? false : true) : true;
            @endphp

            <div x-data="{ open:false }" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 transition-colors duration-200">
                <div class="flex items-start justify-between p-3 {{ $divClass }} dark:bg-opacity-20 transition-colors duration-200">
                    <div>
                    <div class="flex items-center font-medium">
                        {{ \Illuminate\Support\Carbon::parse($t->date)->format('d M, Y') }}
                        @if(($isAdmin ?? false) && ($companyIsAll ?? false))
                        · {{ $t->company?->name ?? '—' }}
                        @endif
                        @if($t->group_id)
                        · <svg xmlns="http://www.w3.org/2000/svg" width="25px" height="25px" viewBox="0 0 24 24" fill="none" class="bg-blue-100/80 dark:bg-blue-900/30 px-[5px] rounded-full ms-1 transition-colors duration-200">
                            <path d="M20 10L4 10L9.5 4" stroke="#044c78" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4 14L20 14L14.5 20" stroke="#044c78" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        @endif
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 mb-2 pb-3 border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
                        {{ $t->category?->name ?? '—' }} · {{ $t->account?->name ?? '—' }}
                    </div>
                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        @if($t->description)
                            {!! $t->description !!}
                        @else
                            {{ $t->category?->name ?? '—' }}
                        @endif
                    </div>
                    </div>
                    <div class="ml-3 text-right {{ $amtClass }} font-semibold">
                        <span class="whitespace-nowrap">{{ $sign }}{{ format_money($t->amount, $rowSym) }}</span>
                        <div class="mt-2 flex items-center justify-end gap-3">
                            @if($can_modify)
                                <button type="button" class="text-blue-600 underline text-sm" @click="open = !open">
                                    <span x-show="!open">
                                        <svg viewBox="0 0 48 48" class="h-4 w-4"><defs><style>.cls-1{fill:#fc6}.cls-7{fill:#ffba55}</style></defs><g id="pencil"><path class="cls-1" d="M40.58 15.75 12.81 43.53l-1.56-2.59c-.65-1.08-.07-.74-2.61-1.58-.85-2.54-.49-2-1.59-2.61L4.47 35.2 32.25 7.42z"/><path d="M39.58 14.75C18.81 35.52 19 36 15.24 37a10.35 10.35 0 0 1-9.77-2.8L32.25 7.42z" style="fill:#ffde76"/><path d="m12.81 43.53-6 1.75a8.76 8.76 0 0 0-4.12-4.12c.68-2.3.28-.93 1.75-6l3.47 2.08.7 2.08 2.08.7c1.31 2.21.85 1.41 2.12 3.51z" style="fill:#f6ccaf"/><path d="M11.75 41.78c-4.49.81-7.52-1.83-8.52-2.35l1.24-4.23 3.47 2.08.7 2.08 2.08.7z" style="fill:#ffdec7"/><path d="M6.84 45.28c0 .1.09 0-5.84 1.72.81-2.76.42-1.45 1.72-5.84a8.85 8.85 0 0 1 4.12 4.12z" style="fill:#374f68"/><path d="m5.78 43.6-4.14 1.21 1.08-3.65a8.67 8.67 0 0 1 3.06 2.44z" style="fill:#425b72"/><path class="cls-7" d="M38.51 13.68 11.25 40.94c-.64-1.07-.26-.79-1.58-1.24L37.1 12.27zM35.74 10.91 8.3 38.34c-.45-1.33-.17-1-1.25-1.59L34.32 9.49z"/><path class="cls-1" d="M35.74 10.91 9.83 36.81a10.59 10.59 0 0 1-2-.84L34.32 9.49z"/><path d="M46.14 10.2 43.36 13 35 4.64l2.8-2.78a3 3 0 0 1 4.17 0L46.14 6a3 3 0 0 1 0 4.2z" style="fill:#db5669"/><path d="M46.83 7.11c-.77 2.2-4.18 3.15-6.25 1.08L36 3.64l1.8-1.78a3 3 0 0 1 4.17 0c4.61 4.61 4.58 4.45 4.86 5.25z" style="fill:#f26674"/><path d="m43.36 13-2.78 2.78-8.33-8.36L35 4.64z" style="fill:#dad7e5"/><path d="M42.36 12a2.52 2.52 0 0 1-3.56 0l-5.55-5.58L35 4.64z" style="fill:#edebf2"/><path class="cls-1" d="M38.51 13.68 15.24 37a10.69 10.69 0 0 1-3.09.27l25-24.95z"/></g></svg>
                                    </span>
                                    <span x-show="open">
                                        <svg viewBox="0 0 16 16" class="h-5 w-5"><path d="m4.12 6.137 1.521-1.52 7 7-1.52 1.52z"/><path d="m4.12 11.61 7.001-7 1.52 1.52-7 7z"/></svg>
                                    </span>
                                </button>
                                <form action="{{ route('admin.transactions.destroy', $t) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Delete this transaction?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 underline text-sm">
                                        <svg x="0" y="0" viewBox="0 0 256 256" class="h-5 w-5" style="enable-background:new 0 0 256 256" xml:space="preserve"><style>.st11{fill:#6770e6}.st12{fill:#5861c7}.st16{fill:#858eff}</style><path class="st11" d="M197 70H59c-8.837 0-16 7.163-16 16v14h170V86c0-8.837-7.163-16-16-16z"/><path class="st16" d="M197 70H59c-8.837 0-16 7.164-16 16v6c0-8.836 7.163-16 16-16h138c8.837 0 16 7.164 16 16v-6c0-8.836-7.163-16-16-16z"/><path class="st12" d="M169 70h-12v-4c0-5.514-4.486-10-10-10h-38c-5.514 0-10 4.486-10 10v4H87v-4c0-12.131 9.869-22 22-22h38c12.131 0 22 9.869 22 22v4z"/><path class="st11" d="M147 44h-38c-12.131 0-22 9.869-22 22v4h.095C88.109 58.803 97.544 50 109 50h38c11.456 0 20.891 8.803 21.905 20H169v-4c0-12.131-9.869-22-22-22z"/><path class="st16" d="M215 116H41a8 8 0 0 1 0-16h174a8 8 0 0 1 0 16z"/><path class="st11" d="M213 116H43l18.038 126.263A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737L213 116z"/><path class="st12" d="M179.944 250H76.056c-7.23 0-13.464-4.682-15.527-11.303l.509 3.565A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737l.509-3.565c-2.063 6.62-8.297 11.302-15.528 11.302zM82.665 136h-.93c-4.141 0-7.377 3.576-6.965 7.697l8.6 86A7 7 0 0 0 90.335 236h.93c4.141 0 7.377-3.576 6.965-7.697l-8.6-86A7 7 0 0 0 82.665 136zM165.165 236h-.93c-4.141 0-7.377-3.576-6.965-7.697l8.6-86a7 7 0 0 1 6.965-6.303h.93c4.141 0 7.377 3.576 6.965 7.697l-8.6 86a7 7 0 0 1-6.965 6.303zM128.5 136h-1a7 7 0 0 0-7 7v86a7 7 0 0 0 7 7h1a7 7 0 0 0 7-7v-86a7 7 0 0 0-7-7z"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                

                {{-- Inline edit (mobile) --}}
                @php $mDescId = 'm-desc-'.$t->id; @endphp
                <div x-show="open" x-transition x-cloak x-effect="open ? initTinyById('{{ $mDescId }}') : destroyTinyById('{{ $mDescId }}')" class="mt-3 border-t border-gray-200 dark:border-gray-700 p-3 pt-6 transition-colors duration-200">
                    <div
                    x-data="{
                        type: '{{ old('type_'.$t->id, $t->type) }}',
                        transactionType: '{{ old('transaction_type_'.$t->id, $t->transaction_type) }}',
                        status: '{{ old('status_'.$t->id, $t->status) }}',
                        amountMode: '{{ old('amount_mode_'.$t->id, $t->amount_mode ?? 'base') }}',
                        name: '{{ old('name_'.$t->id, $t->name) }}',
                        invoiceAmount: '{{ old('invoice_amount_'.$t->id, $t->invoice_amount ?? '') }}',
                        tds_rate: '{{ old('tds_rate_'.$t->id, $t->tds_rate ?? '1') }}',
                        date: '{{ old('date_'.$t->id, $t->date) }}',
                        category_id: {{ (int)old('category_id_'.$t->id, $t->category_id) }},
                        account_id:  {{ (int)old('account_id_'.$t->id, $t->account_id) }},
                        amount: '{{ old('amount_'.$t->id, number_format($t->amount,2,'.','')) }}',
                        description: @js(old('description_'.$t->id, $t->description)),
                        inex:  @js($rowCats['InEx'] ?? []),
                        accounts: @js($rowCats['Account'] ?? []),
                        isTransfer: {{ $isTransfer ? 'true' : 'false' }},
                        counter_account_id: {{ (int) old('counter_account_id_'.$t->id, $prefillCounterAcc) }},
                        get gstRate(){ return 0.18 },
                        get baseAmount(){
                            let v = parseFloat(this.amount) || 0;
                            return this.amountMode === 'base' ? v : v / (1 + this.gstRate);
                        },
                        get gstAmount(){ return this.baseAmount * this.gstRate },
                        get totalAmount(){ return this.baseAmount + this.gstAmount },
                        get tdsRateNum(){ return parseFloat(this.tds_rate) || 0 },
                        get tdsAmount(){ return (parseFloat(this.invoiceAmount) || 0) * this.tdsRateNum / 100 },
                        get tdsUsable(){ return (parseFloat(this.invoiceAmount) || 0) - this.tdsAmount },
                        fmt(n){ return '{{ $rowSym }}' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',') },
                        get cats(){ return this.inex },
                        get counterLabel(){
                            return this.type === 'Income' ? 'Expense Account'
                                : (this.type === 'Expense' ? 'Income Account' : 'Counter Account');
                        },
                        get counterPlaceholder(){
                            return this.type === 'Income' ? 'Select Expense Account'
                                : (this.type === 'Expense' ? 'Select Income Account' : 'Select Account');
                        },
                    }"
                    x-init="$nextTick(() => {
                                category_id = Number(category_id) || null;
                                account_id  = Number(account_id)  || null;
                            });
                            $watch('type', () => {
                                const arr = cats;
                                if (!arr.find(c => Number(c.id) === Number(category_id))) {
                                category_id = arr.length ? Number(arr[0].id) : null;
                                }
                            })"
                    class="grid gap-3"
                    >
                    <form method="POST" action="{{ route('admin.transactions.update', $t) }}" class="grid gap-3">
                        @csrf @method('PUT')
                        <input type="hidden" name="redirect" value="{{ url()->full() }}">
                        <input type="hidden" name="edit_id" value="{{ $t->id }}">

                        <div class="grid grid-cols-2 gap-3">
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Transaction Type</label>
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $txTypes = [
                                            'gst'    => ['icon' => '🧾', 'title' => 'GST',        'sub' => '18% tax'],
                                            'tds'    => ['icon' => '🏛️', 'title' => 'TDS',        'sub' => 'govt deduct'],
                                            'exempt' => ['icon' => '🏦', 'title' => 'Tax Exempt', 'sub' => 'bank, no tax'],
                                            'cash'   => ['icon' => '💵', 'title' => 'Cash',       'sub' => 'physical cash'],
                                        ];
                                    @endphp
                                    @foreach($txTypes as $val => $info)
                                        <label class="cursor-pointer" @click="transactionType = '{{ $val }}'">
                                            <input type="radio" name="transaction_type" value="{{ $val }}"
                                                x-model="transactionType"
                                                class="sr-only"
                                                {{ old('transaction_type_'.$t->id, $t->transaction_type) === $val ? 'checked' : '' }}>
                                            <div class="flex flex-col items-center justify-center w-28 h-20 rounded-xl border-2 px-2 py-2 text-center transition-all"
                                                :class="transactionType === '{{ $val }}'
                                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 dark:border-blue-400 shadow-sm'
                                                    : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                <span class="text-xl leading-none mb-1">{{ $info['icon'] }}</span>
                                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $info['title'] }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $info['sub'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('transaction_type')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Date</label>
                                <input type="date" name="date" x-model="date" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Type</label>
                                <select name="type" x-model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                <option value="Income">Income</option>
                                <option value="Expense">Expense</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div x-show="transactionType === 'gst' || transactionType === 'exempt' || transactionType === 'cash'">
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Amount</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-2.5 text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ $rowSym }}</span>
                                    <input type="number" step="0.01" name="amount" x-model="amount" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 pl-7 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Account</label>
                                <select name="account_id" x-model.number="account_id" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                    <template x-if="accounts.length === 0"><option value="">No accounts</option></template>
                                    <template x-for="a in accounts" :key="a.id">
                                        <option
                                        :value="+a.id"
                                        x-text="a.name"
                                        :selected="Number(a.id) === Number(account_id)"
                                        ></option>
                                    </template>
                                </select>
                            </div>
                            <div x-show="!isTransfer">
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Category</label>
                                <select name="category_id" x-model.number="category_id" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                <template x-if="cats.length === 0"><option value="">No categories</option></template>
                                <template x-for="c in cats" :key="c.id">
                                    <option
                                    :value="Number(c.id)"
                                    x-text="c.name"
                                    :selected="Number(c.id) === Number(category_id)"
                                    ></option>
                                </template>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Payment Status</label>
                                <select name="status" x-model="status" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                    <option value="">Select Status</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Client / Vendor</label>
                                <input type="text" name="name" x-model="name" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                            </div>

                            <div x-show="transactionType === 'gst'" class="col-span-2">
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Amount Mode</label>
                                <div class="flex rounded border border-gray-300 dark:border-gray-600 overflow-hidden">
                                    <button type="button"
                                        @click="amountMode = 'base'"
                                        :class="amountMode === 'base' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                        class="flex-1 px-3 py-2 text-sm font-medium transition-colors">
                                        Base (excl. GST)
                                    </button>
                                    <button type="button"
                                        @click="amountMode = 'total'"
                                        :class="amountMode === 'total' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                        class="flex-1 px-3 py-2 text-sm font-medium border-l border-gray-300 dark:border-gray-600 transition-colors">
                                        Total (incl. GST)
                                    </button>
                                </div>
                                <input type="hidden" name="amount_mode" :value="amountMode">
                            </div>

                            {{-- GST Breakdown (mobile) --}}
                            <div x-show="transactionType === 'gst' && parseFloat(amount) > 0" class="col-span-2">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-sm space-y-1 transition-colors duration-200">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Base Amount</span>
                                        <span class="text-gray-900 dark:text-gray-100" x-text="fmt(baseAmount)"></span>
                                    </div>
                                    <div class="flex justify-between text-blue-600 dark:text-blue-400">
                                        <span>GST @ 18%</span>
                                        <span x-text="'+ ' + fmt(gstAmount)"></span>
                                    </div>
                                    <div class="flex justify-between font-semibold border-t border-gray-200 dark:border-gray-600 pt-1 text-gray-900 dark:text-gray-100">
                                        <span>Bank Received</span>
                                        <span x-text="fmt(totalAmount)"></span>
                                    </div>
                                    <div class="flex justify-between text-orange-500 dark:text-orange-400">
                                        <span>GST Locked (owed to govt)</span>
                                        <span x-text="'− ' + fmt(gstAmount)"></span>
                                    </div>
                                    <div class="flex justify-between font-semibold text-green-700 dark:text-green-400">
                                        <span>Usable Amount</span>
                                        <span x-text="fmt(baseAmount)"></span>
                                    </div>
                                </div>
                                <input type="hidden" name="base"      :value="transactionType === 'gst' ? baseAmount.toFixed(2) : ''">
                                <input type="hidden" name="gst"       :value="transactionType === 'gst' ? gstAmount.toFixed(2) : ''">
                                <input type="hidden" name="gstLocked" :value="transactionType === 'gst' ? gstAmount.toFixed(2) : ''">
                                <input type="hidden" name="usable"    :value="transactionType === 'gst' ? baseAmount.toFixed(2) : ''">
                                <input type="hidden" name="netRec"    :value="transactionType === 'gst' ? totalAmount.toFixed(2) : ''">
                            </div>

                            {{-- TDS fields (mobile) --}}
                            <div x-show="transactionType === 'tds'">
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Invoice Amount</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-2.5 text-gray-500 dark:text-gray-400">{{ $rowSym }}</span>
                                    <input type="number" step="0.01" name="invoice_amount" x-model="invoiceAmount" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 pl-7 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                </div>
                            </div>
                            <div x-show="transactionType === 'tds'">
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">TDS Rate</label>
                                <select name="tds_rate" x-model="tds_rate" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                    <option value="1">1%</option>
                                    <option value="2">2%</option>
                                </select>
                            </div>

                            {{-- TDS Breakdown (mobile) --}}
                            <div x-show="transactionType === 'tds' && parseFloat(invoiceAmount) > 0" class="col-span-2">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-sm space-y-1 transition-colors duration-200">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Invoice Amount</span>
                                        <span class="text-gray-900 dark:text-gray-100" x-text="fmt(parseFloat(invoiceAmount) || 0)"></span>
                                    </div>
                                    <div class="flex justify-between text-red-600 dark:text-red-400">
                                        <span x-text="'TDS @ ' + tds_rate + '% (held by govt)'"></span>
                                        <span x-text="'− ' + fmt(tdsAmount)"></span>
                                    </div>
                                    <div class="flex justify-between font-semibold border-t border-gray-200 dark:border-gray-600 pt-1 text-green-700 dark:text-green-400">
                                        <span>Bank Received (usable)</span>
                                        <span x-text="fmt(tdsUsable)"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Counter Account: show only if transfer --}}
                            <div x-show="isTransfer" x-cloak>
                                <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">
                                    <span x-text="counterLabel"></span>
                                </label>
                                <select name="counter_account_id"
                                        x-model.number="counter_account_id"
                                        :required="isTransfer"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                    <option value="" x-text="counterPlaceholder"></option>
                                    <template x-for="a in accounts" :key="a.id">
                                        <option :value="+a.id" x-text="a.name" :selected="Number(a.id) === Number(counter_account_id)"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">Description</label>
                            <textarea id="{{ $mDescId }}" name="description" x-model="description" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button class="px-3 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded text-sm hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">Save</button>
                            <button type="button" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" @click="open=false">Cancel</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 dark:text-gray-400 py-6 transition-colors duration-200">No transactions found.</p>
        @endforelse

        <div class="pt-1">{{ $transactions->links() }}</div>
    </div>

    {{-- DESKTOP/TABLE --}}
    <div class="hidden md:block rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 transition-colors duration-200">
        {{-- Filter toolbar: two rows, auto-grid, icons in inputs --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.transactions.index') }}"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 transition-colors duration-200">

                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-center">
                    {{-- Start date --}}
                    <div class="relative">
                        <label class="sr-only">Start date</label>
                        <input type="date" name="start_date"
                            value="{{ old('start_date', $clampedStart ?? $startDate) }}"
                            min="{{ $startDate }}" max="{{ $endDate }}"
                            class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200"
                            onchange="this.form.submit()"
                        />
                    </div>

                    {{-- End date --}}
                    <div class="relative">
                        <label class="sr-only">End date</label>
                        <input type="date" name="end_date"
                            value="{{ old('end_date', $clampedEnd ?? $endDate) }}"
                            min="{{ $startDate }}" max="{{ $endDate }}"
                            class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200"
                            onchange="this.form.submit()"
                        />
                    </div>

                    {{-- Type --}}
                    <div class="relative">
                        <label class="sr-only">Type</label>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <!-- tag icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24">
                            <path d="M17.0020048,13 C17.5542895,13 18.0020048,13.4477153 18.0020048,14 C18.0020048,14.5128358 17.6159646,14.9355072 17.1186259,14.9932723 L17.0020048,15 L5.41700475,15 L8.70911154,18.2928932 C9.0695955,18.6533772 9.09732503,19.2206082 8.79230014,19.6128994 L8.70911154,19.7071068 C8.34862757,20.0675907 7.78139652,20.0953203 7.38910531,19.7902954 L7.29489797,19.7071068 L2.29489797,14.7071068 C1.69232289,14.1045317 2.07433707,13.0928192 2.88837381,13.0059833 L3.00200475,13 L17.0020048,13 Z M16.6128994,4.20970461 L16.7071068,4.29289322 L21.7071068,9.29289322 C22.3096819,9.8954683 21.9276677,10.9071808 21.1136309,10.9940167 L21,11 L7,11 C6.44771525,11 6,10.5522847 6,10 C6,9.48716416 6.38604019,9.06449284 6.88337887,9.00672773 L7,9 L18.585,9 L15.2928932,5.70710678 C14.9324093,5.34662282 14.9046797,4.77939176 15.2097046,4.38710056 L15.2928932,4.29289322 C15.6533772,3.93240926 16.2206082,3.90467972 16.6128994,4.20970461 Z"/>
                            </svg>
                        </div>
                        <select name="type" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 pl-9 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200" onchange="this.form.submit()">
                            <option value="">All Type</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}" {{ ($fType ?? request('type')) === $t ? 'selected' : '' }}>
                                    {{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Account --}}
                    <div class="relative">
                        <label class="sr-only">Account</label>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none" onchange="this.form.submit()">
                            <!-- wallet/account icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" data-name="Layer 1" id="Layer_1"><title/><path d="M19,4H5A3,3,0,0,0,2,7V17a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V7A3,3,0,0,0,19,4Zm1,10.3H17a2.3,2.3,0,0,1,0-4.6h3ZM20,8H17a4,4,0,0,0,0,8h3v1a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V7A1,1,0,0,1,5,6H19a1,1,0,0,1,1,1Zm-4,4a1,1,0,1,0,1-1A1,1,0,0,0,16,12Z"/></svg>
                        </div>
                        <select name="account_id" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 pl-9 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">
                            <option value="0">All Account</option>
                            @foreach($accountChoices as $acc)
                                <option value="{{ $acc->id }}" {{ ((int)($fAccount ?? request('account_id')) === $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->name }}
                                    @if($isAdmin && $companyIsAll) ({{ $acc->company->name ?? '' }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-3 items-center">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center w-full">
                            <div class="relative w-full">
                                <label class="sr-only">Search</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <!-- search icon -->
                                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"/>
                                    </svg>
                                </div>
                                <input type="search" name="q" placeholder="Search description, category, account, amount"
                                    value="{{ request('q', $fSearch ?? '') }}"
                                    class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 pl-9 text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200" />
                            </div>
                        </div>
                        
                        <div class="flex justify-start sm:justify-center md:justify-start">
                            <button type="submit" class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded-md text-sm hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">
                                Filter
                            </button>
                            <a href="{{ route('admin.transactions.index') }}" class="ml-3 text-sm underline text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200">Reset</a>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <label class="sr-only">Per page</label>
                        <select name="per_page" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm w-32 text-gray-900 dark:text-gray-100 transition-colors duration-200" onchange="this.form.submit()">
                            @foreach([10,15,25,50,100,500] as $n)
                                <option value="{{ $n }}" {{ (int) request('per_page', $perPage ?? 15) === $n ? 'selected' : '' }}>
                                    {{ $n }} / page
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </form>
        </div>

        @php
            function sort_link($label, $column, $currentSort, $currentDir) {
                $dir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
                $arrow = '';
                if ($currentSort === $column) {
                    $arrow = $currentDir === 'asc' ? ' ▲' : ' ▼';
                }
                $url = request()->fullUrlWithQuery(['sort'=>$column, 'dir'=>$dir, 'page'=>null]);
                return '<a href="'.$url.'" class="underline">'.$label.$arrow.'</a>';
            }
        @endphp

        <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="text-left">
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('Date', 'date', $sort, $dir) !!}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('Type', 'type', $sort, $dir) !!}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('Category', 'category', $sort, $dir) !!}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('Account', 'account', $sort, $dir) !!}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('Amount', 'amount', $sort, $dir) !!}</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Description</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Actions</th>
                    </tr>
                </thead>
                <tbody x-data="{ openId: {{ old('edit_id') ? (int) old('edit_id') : 'null' }} }">
                    @php
                        $colspan = 7;  // Date,(Company),Type,Category,Account,Amount,Desc,Actions
                    @endphp
                    @forelse($transactions as $t)
                        @php
                            $isExpense          = $t->type === 'Expense';
                            $divClass           = $isExpense ? 'bg-red-200/25 dark:bg-red-900/20' : 'bg-green-100/25 dark:bg-green-900/20';
                            $isTransfer         = $t->main_transaction_id !== null || $t->mirrors()->exists();
                            $can_modify         = $isTransfer ? ($t->main_transaction_id ? false : true) : true;
                        @endphp
                        {{-- DISPLAY ROW --}}
                        <tr class="{{ $divClass }} odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                            <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                {{ \Illuminate\Support\Carbon::parse($t->date)->toDateString() }}
                                @if($t->group_id)
                                · <svg xmlns="http://www.w3.org/2000/svg" width="25px" height="25px" viewBox="0 0 24 24" fill="none" class="inline bg-blue-100/80 dark:bg-blue-900/30 px-[5px] rounded-full ms-1 transition-colors duration-200">
                                    <path d="M20 10L4 10L9.5 4" stroke="#044c78" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4 14L20 14L14.5 20" stroke="#044c78" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                @endif
                            </td>

                            @if($isAdmin && $companyIsAll)
                                <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $t->company?->name ?? '—' }}</td>
                            @endif

                            <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $t->type }}
                                @if($t->description === 'Opening Balance')
                                    <span class="ml-1 text-xs rounded bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 text-gray-900 dark:text-gray-100 transition-colors duration-200">auto</span>
                                @endif
                            </td>
                            <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $t->category?->name ?? '—' }}</td>
                            <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $t->account?->name ?? '—' }}</td>

                            <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                @if($isAdmin && $companyIsAll)
                                    {{ format_money($t->amount, $t->company?->currency_symbol) }}
                                @else
                                    {{ format_money($t->amount, $currencySymbol) }}
                                @endif
                            </td>

                            <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! $t->description !!}</td>

                            <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 whitespace-nowrap text-center">
                                @if($can_modify)
                                    <button type="button" class="text-blue-600 underline"
                                            @click="openId = (openId === {{ $t->id }}) ? null : {{ $t->id }}">
                                        <span x-show="openId !== {{ $t->id }}">
                                            <svg viewBox="0 0 48 48" class="h-4 w-4"><defs><style>.cls-1{fill:#fc6}.cls-7{fill:#ffba55}</style></defs><g id="pencil"><path class="cls-1" d="M40.58 15.75 12.81 43.53l-1.56-2.59c-.65-1.08-.07-.74-2.61-1.58-.85-2.54-.49-2-1.59-2.61L4.47 35.2 32.25 7.42z"/><path d="M39.58 14.75C18.81 35.52 19 36 15.24 37a10.35 10.35 0 0 1-9.77-2.8L32.25 7.42z" style="fill:#ffde76"/><path d="m12.81 43.53-6 1.75a8.76 8.76 0 0 0-4.12-4.12c.68-2.3.28-.93 1.75-6l3.47 2.08.7 2.08 2.08.7c1.31 2.21.85 1.41 2.12 3.51z" style="fill:#f6ccaf"/><path d="M11.75 41.78c-4.49.81-7.52-1.83-8.52-2.35l1.24-4.23 3.47 2.08.7 2.08 2.08.7z" style="fill:#ffdec7"/><path d="M6.84 45.28c0 .1.09 0-5.84 1.72.81-2.76.42-1.45 1.72-5.84a8.85 8.85 0 0 1 4.12 4.12z" style="fill:#374f68"/><path d="m5.78 43.6-4.14 1.21 1.08-3.65a8.67 8.67 0 0 1 3.06 2.44z" style="fill:#425b72"/><path class="cls-7" d="M38.51 13.68 11.25 40.94c-.64-1.07-.26-.79-1.58-1.24L37.1 12.27zM35.74 10.91 8.3 38.34c-.45-1.33-.17-1-1.25-1.59L34.32 9.49z"/><path class="cls-1" d="M35.74 10.91 9.83 36.81a10.59 10.59 0 0 1-2-.84L34.32 9.49z"/><path d="M46.14 10.2 43.36 13 35 4.64l2.8-2.78a3 3 0 0 1 4.17 0L46.14 6a3 3 0 0 1 0 4.2z" style="fill:#db5669"/><path d="M46.83 7.11c-.77 2.2-4.18 3.15-6.25 1.08L36 3.64l1.8-1.78a3 3 0 0 1 4.17 0c4.61 4.61 4.58 4.45 4.86 5.25z" style="fill:#f26674"/><path d="m43.36 13-2.78 2.78-8.33-8.36L35 4.64z" style="fill:#dad7e5"/><path d="M42.36 12a2.52 2.52 0 0 1-3.56 0l-5.55-5.58L35 4.64z" style="fill:#edebf2"/><path class="cls-1" d="M38.51 13.68 15.24 37a10.69 10.69 0 0 1-3.09.27l25-24.95z"/></g></svg>
                                        </span>
                                        <span x-show="openId === {{ $t->id }}">
                                            <svg viewBox="0 0 16 16" class="h-5 w-5"><path d="m4.12 6.137 1.521-1.52 7 7-1.52 1.52z"/><path d="m4.12 11.61 7.001-7 1.52 1.52-7 7z"/></svg>
                                        </span>
                                    </button>

                                    <form action="{{ route('admin.transactions.destroy', $t) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this transaction?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 underline ml-2">
                                            <svg x="0" y="0" viewBox="0 0 256 256" class="h-5 w-5" style="enable-background:new 0 0 256 256" xml:space="preserve"><style>.st11{fill:#6770e6}.st12{fill:#5861c7}.st16{fill:#858eff}</style><path class="st11" d="M197 70H59c-8.837 0-16 7.163-16 16v14h170V86c0-8.837-7.163-16-16-16z"/><path class="st16" d="M197 70H59c-8.837 0-16 7.164-16 16v6c0-8.836 7.163-16 16-16h138c8.837 0 16 7.164 16 16v-6c0-8.836-7.163-16-16-16z"/><path class="st12" d="M169 70h-12v-4c0-5.514-4.486-10-10-10h-38c-5.514 0-10 4.486-10 10v4H87v-4c0-12.131 9.869-22 22-22h38c12.131 0 22 9.869 22 22v4z"/><path class="st11" d="M147 44h-38c-12.131 0-22 9.869-22 22v4h.095C88.109 58.803 97.544 50 109 50h38c11.456 0 20.891 8.803 21.905 20H169v-4c0-12.131-9.869-22-22-22z"/><path class="st16" d="M215 116H41a8 8 0 0 1 0-16h174a8 8 0 0 1 0 16z"/><path class="st11" d="M213 116H43l18.038 126.263A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737L213 116z"/><path class="st12" d="M179.944 250H76.056c-7.23 0-13.464-4.682-15.527-11.303l.509 3.565A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737l.509-3.565c-2.063 6.62-8.297 11.302-15.528 11.302zM82.665 136h-.93c-4.141 0-7.377 3.576-6.965 7.697l8.6 86A7 7 0 0 0 90.335 236h.93c4.141 0 7.377-3.576 6.965-7.697l-8.6-86A7 7 0 0 0 82.665 136zM165.165 236h-.93c-4.141 0-7.377-3.576-6.965-7.697l8.6-86a7 7 0 0 1 6.965-6.303h.93c4.141 0 7.377 3.576 6.965 7.697l-8.6 86a7 7 0 0 1-6.965 6.303zM128.5 136h-1a7 7 0 0 0-7 7v86a7 7 0 0 0 7 7h1a7 7 0 0 0 7-7v-86a7 7 0 0 0-7-7z"/></svg>
                                        </button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>

                        {{-- INLINE EDITOR ROW --}}
                        @php $descId = 'desc-'.$t->id; @endphp
                        <tr x-show="openId === {{ $t->id }}" x-transition x-cloak 
                            x-effect="(openId === {{ $t->id }}) ? initTinyById('{{ $descId }}') : destroyTinyById('{{ $descId }}')"
                            @keydown.escape.window="openId = null">
                            <td colspan="{{ $colspan }}" class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                                @php
                                    $companyId          = $t->company_id;
                                    $sym                = $currencyByCompany[$companyId] ?? ($t->company->currency_symbol ?? '');
                                    $rowCats            = $categoryMap[$companyId] ?? ['Income'=>[],'Expense'=>[],'Account'=>[], 'InEx'=>[]];
                                    $accountsForCompany = $categoryMap[$t->company_id]['Account'] ?? [];
                                    $counter            = $t->main_transaction_id ? $t : $t->mirrors()->first();
                                    $prefillCounterAcc  = $counter?->id === $t->id ? $t->account_id : ($counter?->account_id ?? null);
                                @endphp

                                <div
                                    x-data="{
                                        type: '{{ old('type_'.$t->id, $t->type) }}',
                                        transactionType: '{{ old('transaction_type_'.$t->id, $t->transaction_type) }}',
                                        status: '{{ old('status_'.$t->id, $t->status) }}',
                                        amountMode: '{{ old('amount_mode_'.$t->id, $t->amount_mode ?? 'base') }}',
                                        name: '{{ old('name_'.$t->id, $t->name) }}',
                                        invoiceAmount: '{{ old('invoice_amount_'.$t->id, $t->invoice_amount ?? '') }}',
                                        tds_rate: '{{ old('tds_rate_'.$t->id, $t->tds_rate ?? '1') }}',
                                        date: '{{ old('date_'.$t->id, $t->date) }}',
                                        category_id: {{ (int)old('category_id_'.$t->id, $t->category_id) }},
                                        account_id:  {{ (int)old('account_id_'.$t->id, $t->account_id) }},
                                        amount: '{{ old('amount_'.$t->id, number_format($t->amount,2,'.','')) }}',
                                        description: @js(old('description_'.$t->id, $t->description)),
                                        inex:@js($rowCats['InEx']),
                                        accounts:@js($rowCats['Account']),
                                        isTransfer: {{ $isTransfer ? 'true' : 'false' }},
                                        counter_account_id: {{ (int) old('counter_account_id_'.$t->id, $prefillCounterAcc) }},
                                        get gstRate(){ return 0.18 },
                                        get baseAmount(){
                                            let v = parseFloat(this.amount) || 0;
                                            return this.amountMode === 'base' ? v : v / (1 + this.gstRate);
                                        },
                                        get gstAmount(){ return this.baseAmount * this.gstRate },
                                        get totalAmount(){ return this.baseAmount + this.gstAmount },
                                        get tdsRateNum(){ return parseFloat(this.tds_rate) || 0 },
                                        get tdsAmount(){ return (parseFloat(this.invoiceAmount) || 0) * this.tdsRateNum / 100 },
                                        get tdsUsable(){ return (parseFloat(this.invoiceAmount) || 0) - this.tdsAmount },
                                        fmt(n){ return '{{ $sym }}' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',') },
                                        get cats(){ return this.inex },
                                        get counterLabel(){
                                            return this.type === 'Income' ? 'Expense Account'
                                                : (this.type === 'Expense' ? 'Income Account' : 'Counter Account');
                                        },
                                        get counterPlaceholder(){
                                            return this.type === 'Income' ? 'Select Expense Account'
                                                : (this.type === 'Expense' ? 'Select Income Account' : 'Select Account');
                                        },
                                    }"
                                    x-init="$nextTick(() => {
                                            category_id = Number(category_id) || null;
                                            account_id  = Number(account_id)  || null;
                                        });
                                        $watch('type', () => {
                                            const arr = cats;
                                            if (!arr.find(c => Number(c.id) === Number(category_id))) {
                                            category_id = arr.length ? Number(arr[0].id) : null;
                                            }
                                        })"
                                    class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 transition-colors duration-200"
                                >
                                    <form method="POST" action="{{ route('admin.transactions.update', $t) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="redirect" value="{{ url()->full() }}">
                                        <input type="hidden" name="edit_id" value="{{ $t->id }}">

                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Transaction Type</label>
                                            <div class="flex flex-wrap gap-3">
                                                @php
                                                    $txTypes = [
                                                        'gst'    => ['icon' => '🧾', 'title' => 'GST',        'sub' => '18% tax'],
                                                        'tds'    => ['icon' => '🏛️', 'title' => 'TDS',        'sub' => 'govt deduct'],
                                                        'exempt' => ['icon' => '🏦', 'title' => 'Tax Exempt', 'sub' => 'bank, no tax'],
                                                        'cash'   => ['icon' => '💵', 'title' => 'Cash',       'sub' => 'physical cash'],
                                                    ];
                                                @endphp
                                                @foreach($txTypes as $val => $info)
                                                    <label class="cursor-pointer" @click="transactionType = '{{ $val }}'">
                                                        <input type="radio" name="transaction_type" value="{{ $val }}"
                                                            x-model="transactionType"
                                                            class="sr-only"
                                                            {{ old('transaction_type_'.$t->id, $t->transaction_type) === $val ? 'checked' : '' }}>
                                                        <div class="flex flex-col items-center justify-center w-28 h-20 rounded-xl border-2 px-2 py-2 text-center transition-all"
                                                            :class="transactionType === '{{ $val }}'
                                                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 dark:border-blue-400 shadow-sm'
                                                                : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                            <span class="text-xl leading-none mb-1">{{ $info['icon'] }}</span>
                                                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $info['title'] }}</span>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $info['sub'] }}</span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('transaction_type')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Date</label>
                                            <input type="date" name="date" x-model="date" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Type</label>
                                            <select name="type" x-model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                                <option value="Income">Income</option>
                                                <option value="Expense">Expense</option>
                                            </select>
                                        </div>

                                        <div x-show="transactionType === 'gst' || transactionType === 'exempt' || transactionType === 'cash'">
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Amount</label>
                                            <div class="relative">
                                                <span class="absolute left-2 top-2.5 text-gray-500 dark:text-gray-400 transition-colors duration-200">{{ $sym }}</span>
                                                <input type="number" step="0.01" name="amount" x-model="amount" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 pl-7 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Account</label>
                                            <select name="account_id" x-model.number="account_id" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                            <template x-for="a in accounts" :key="a.id">
                                                <option
                                                :value="+a.id"
                                                x-text="a.name"
                                                :selected="Number(a.id) === Number(account_id)"
                                                ></option>
                                            </template>
                                            </select>
                                        </div>

                                        <div x-show="!isTransfer">
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Category</label>
                                            <select name="category_id" x-model.number="category_id" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                                <template x-for="c in cats" :key="c.id">
                                                    <option
                                                    :value="+c.id"
                                                    x-text="c.name"
                                                    :selected="Number(c.id) === Number(category_id)"
                                                    ></option>
                                                </template>
                                            </select>
                                        </div>

                                        {{-- Counter Account: show only if transfer --}}
                                        <div x-show="isTransfer" x-cloak>
                                            <label class="block text-xs font-medium mb-1 text-gray-900 dark:text-gray-100">
                                                <span x-text="counterLabel"></span>
                                            </label>
                                            <select name="counter_account_id"
                                                    x-model.number="counter_account_id"
                                                    :required="isTransfer"
                                                    class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                                <option value="" x-text="counterPlaceholder"></option>
                                                <template x-for="a in accounts" :key="a.id">
                                                    <option :value="+a.id" x-text="a.name" :selected="Number(a.id) === Number(counter_account_id)"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Payment Status</label>
                                            <select name="status" x-model="status" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                                <option value="">Select Status</option>
                                                <option value="paid">Paid</option>
                                                <option value="pending">Pending</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Client / Vendor</label>
                                            <input type="text" name="name" x-model="name" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                        </div>

                                        <div x-show="transactionType === 'gst'">
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Amount Mode</label>
                                            <div class="flex rounded border border-gray-300 dark:border-gray-600 overflow-hidden">
                                                <button type="button"
                                                    @click="amountMode = 'base'"
                                                    :class="amountMode === 'base' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                                    class="flex-1 px-3 py-2 text-sm font-medium transition-colors">
                                                    Base (excl. GST)
                                                </button>
                                                <button type="button"
                                                    @click="amountMode = 'total'"
                                                    :class="amountMode === 'total' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                                    class="flex-1 px-3 py-2 text-sm font-medium border-l border-gray-300 dark:border-gray-600 transition-colors">
                                                    Total (incl. GST)
                                                </button>
                                            </div>
                                            <input type="hidden" name="amount_mode" :value="amountMode">
                                        </div>

                                        {{-- GST Breakdown (desktop) --}}
                                        <div x-show="transactionType === 'gst' && parseFloat(amount) > 0" class="sm:col-span-2 lg:col-span-3">
                                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-sm space-y-1 transition-colors duration-200">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Base Amount</span>
                                                    <span class="text-gray-900 dark:text-gray-100" x-text="fmt(baseAmount)"></span>
                                                </div>
                                                <div class="flex justify-between text-blue-600 dark:text-blue-400">
                                                    <span>GST @ 18%</span>
                                                    <span x-text="'+ ' + fmt(gstAmount)"></span>
                                                </div>
                                                <div class="flex justify-between font-semibold border-t border-gray-200 dark:border-gray-600 pt-1 text-gray-900 dark:text-gray-100">
                                                    <span>Bank Received</span>
                                                    <span x-text="fmt(totalAmount)"></span>
                                                </div>
                                                <div class="flex justify-between text-orange-500 dark:text-orange-400">
                                                    <span>GST Locked (owed to govt)</span>
                                                    <span x-text="'− ' + fmt(gstAmount)"></span>
                                                </div>
                                                <div class="flex justify-between font-semibold text-green-700 dark:text-green-400">
                                                    <span>Usable Amount</span>
                                                    <span x-text="fmt(baseAmount)"></span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="base"      :value="transactionType === 'gst' ? baseAmount.toFixed(2) : ''">
                                            <input type="hidden" name="gst"       :value="transactionType === 'gst' ? gstAmount.toFixed(2) : ''">
                                            <input type="hidden" name="gstLocked" :value="transactionType === 'gst' ? gstAmount.toFixed(2) : ''">
                                            <input type="hidden" name="usable"    :value="transactionType === 'gst' ? baseAmount.toFixed(2) : ''">
                                            <input type="hidden" name="netRec"    :value="transactionType === 'gst' ? totalAmount.toFixed(2) : ''">
                                        </div>

                                        {{-- TDS fields (desktop) --}}
                                        <div x-show="transactionType === 'tds'">
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Invoice Amount</label>
                                            <div class="relative">
                                                <span class="absolute left-2 top-2.5 text-gray-500 dark:text-gray-400">{{ $sym }}</span>
                                                <input type="number" step="0.01" name="invoice_amount" x-model="invoiceAmount" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 pl-7 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                            </div>
                                        </div>
                                        <div x-show="transactionType === 'tds'">
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">TDS Rate</label>
                                            <select name="tds_rate" x-model="tds_rate" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                                <option value="1">1%</option>
                                                <option value="2">2%</option>
                                            </select>
                                        </div>

                                        {{-- TDS Breakdown (desktop) --}}
                                        <div x-show="transactionType === 'tds' && parseFloat(invoiceAmount) > 0" class="sm:col-span-2 lg:col-span-3">
                                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-sm space-y-1 transition-colors duration-200">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Invoice Amount</span>
                                                    <span class="text-gray-900 dark:text-gray-100" x-text="fmt(parseFloat(invoiceAmount) || 0)"></span>
                                                </div>
                                                <div class="flex justify-between text-red-600 dark:text-red-400">
                                                    <span x-text="'TDS @ ' + tds_rate + '% (held by govt)'"></span>
                                                    <span x-text="'− ' + fmt(tdsAmount)"></span>
                                                </div>
                                                <div class="flex justify-between font-semibold border-t border-gray-200 dark:border-gray-600 pt-1 text-green-700 dark:text-green-400">
                                                    <span>Bank Received (usable)</span>
                                                    <span x-text="fmt(tdsUsable)"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">Description</label>
                                            <textarea id="{{ $descId }}" name="description" x-model="description" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200"></textarea>
                                        </div>

                                        <div class="sm:col-span-2 lg:col-span-3 flex gap-2">
                                            <button class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">Save</button>
                                            <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" @click="openId = null">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}" class="p-4 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4"><div class="pagination-custom">{{ $transactions->links() }}</div></div>
    </div>
</x-layouts.admin>
