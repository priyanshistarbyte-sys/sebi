@props([
    'title' => null,
    'breadcrumbs' => [], // [['label'=>'Dashboard','url'=>route('admin.dashboard')], ['label'=>'Users']]
])

<x-app-layout>
    @if($title) @section('title', $title) @endif

    <div x-data="{ sidebarOpen: false , cashModal: {{ $errors->cashTransfer->hasAny(['from_company_id','to_company_id','amount','date','note']) ? 'true' : 'false' }} }">
        <header class="sticky top-0 z-30 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden inline-flex items-center justify-center rounded-md p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                        </svg>
                    </button>

                    {{-- Breadcrumbs --}}
                    <nav class="items-center gap-2 text-sm hidden md:flex">
                        @forelse($breadcrumbs as $i => $crumb)
                            @if(!empty($crumb['url']))
                                <a href="{{ $crumb['url'] }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200">{{ $crumb['label'] }}</a>
                            @else
                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $crumb['label'] }}</span>
                            @endif
                            @if($i < count($breadcrumbs)-1)
                                <span class="text-gray-400 dark:text-gray-500">/</span>
                            @endif
                        @empty
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200">Admin</a>
                            @if($title)<span class="text-gray-400 dark:text-gray-500">/</span><span class="text-gray-900 dark:text-gray-100 font-medium">{{ $title }}</span>@endif
                        @endforelse
                    </nav>
                </div>

                <div class="flex items-center gap-2">
                    @if (session('status'))
                        <span
                            x-data="{ show: true }"
                            x-init="setTimeout(() => show = false, 3000)"
                            x-show="show"
                            x-transition.opacity.duration.300ms
                            class="text-sm text-green-700 dark:text-green-400 transition-colors duration-200"
                        >
                            {{ session('status') }}
                        </span>
                    @endif

                    @php
                        $activeCompanyId = (int) session('active_company_id');
                        $companies = auth()->user()->is_admin
                            ? \App\Models\Company::orderBy('name')->get()
                            : auth()->user()->companies()->orderBy('name')->get();
                    @endphp
                      {{-- Cash Transfer Button --}}
                    <button @click="cashModal = true"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 24 24" fill="none" class="bg-blue-100/80 dark:bg-blue-900/30 px-[4px] rounded-full transition-colors duration-200">
                            <path d="M20 10L4 10L9.5 4" stroke="#044c78" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4 14L20 14L14.5 20" stroke="#044c78" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Cash Transfer</span>
                    </button>

                    {{-- Hidden POST form --}}
                    <form id="companySwitchForm" method="POST" action="{{ route('company.switch') }}" class="hidden">
                        @csrf
                        <input type="hidden" name="company_id" id="company_id_field">
                    </form>

                    {{-- The visible selector (NOT a link) --}}
                    <select class="border rounded p-1.5 text-sm min-w-[8rem]"
                            onchange="
                            document.getElementById('company_id_field').value=this.value;
                            document.getElementById('companySwitchForm').submit();
                        ">
                        @foreach($companies as $co)
                            <option value="{{ $co->id }}" {{ $activeCompanyId === $co->id ? 'selected' : '' }}>
                            {{ $co->name }}
                            </option>
                        @endforeach
                    </select>


                    <!-- Month picker -->
                    @php
                        $activePeriod = session('active_period') ?: now()->format('Y-m'); // "YYYY-MM"
                    @endphp

                    {{-- Hidden, standalone POST form for period --}}
                    <form id="periodSwitchForm" method="POST" action="{{ route('period.switch') }}" class="hidden">
                        @csrf
                        <input type="hidden" name="period" id="period_field">
                    </form>

                    {{-- Native month input (YYYY-MM). Most browsers show a nice Month picker. --}}
                    <label class="text-sm text-gray-600 sr-only" for="period_input">Month</label>
                    <!-- <input
                        id="period_input"
                        type="month"
                        class="border rounded p-1.5 text-sm min-w-[11rem]"
                        value="{{ $activePeriod }}"
                        onchange="
                            document.getElementById('period_field').value=this.value;
                            document.getElementById('periodSwitchForm').submit();
                        "
                    /> -->
                    @php
                        [$yy, $mm] = explode('-', $activePeriod);
                    @endphp
                    <div class="flex items-center gap-1">
                        <select class="border border-gray-300 dark:border-gray-600 rounded p-1.5 text-sm min-w-14 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200"
                                onchange="
                                    const y=document.getElementById('period_year').value;
                                    document.getElementById('period_field').value = y + '-' + this.value;
                                    document.getElementById('periodSwitchForm').submit();
                                ">
                            @foreach(range(1,12) as $m)
                                @php $v = str_pad($m,2,'0',STR_PAD_LEFT); @endphp
                                <option value="{{ $v }}" {{ $mm===$v?'selected':'' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                        <span class="text-gray-400 dark:text-gray-500">/</span>
                        <select id="period_year" class="border border-gray-300 dark:border-gray-600 rounded p-1.5 text-sm min-w-20 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200"
                                onchange="
                                    const m=document.querySelector('[name=month_mm]').value ?? '{{ $mm }}';
                                    document.getElementById('period_field').value = this.value + '-' + m;
                                    document.getElementById('periodSwitchForm').submit();
                                ">
                            @foreach(range(now()->year-5, now()->year+5) as $y)
                                <option value="{{ $y }}" {{ (int)$yy===$y?'selected':'' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        {{-- hidden field to keep current month for the year change handler --}}
                        <input type="hidden" name="month_mm" value="{{ $mm }}">
                    </div>

                </div>
            </div>
            
        </header>

        <div class="flex" style="min-height: calc(100vh - 122px);">
            <aside class="hidden lg:block lg:w-64 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 transition-colors duration-200">
                <x-admin.sidebar />
            </aside>

            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 dark:bg-black/60 lg:hidden" @click="sidebarOpen = false"></div>
            <aside x-show="sidebarOpen" x-transition class="fixed z-50 inset-y-0 left-0 w-72 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 p-2 lg:hidden transition-colors duration-200" @keydown.escape.window="sidebarOpen=false">
                <x-admin.sidebar />
            </aside>

            <main class="flex-1 bg-gray-50 dark:bg-gray-950 transition-colors duration-200">
                <div class="mx-auto max-w-full p-6">
                    {{-- big banner inside the main content --}}
                    @if (session('status'))
                        <div
                            x-data="{ show: true }"
                            x-init="setTimeout(() => show = false, 3000)"
                            x-show="show"
                            x-transition.opacity.duration.300ms
                            class="mb-4 rounded bg-green-50 dark:bg-green-900 dark:bg-opacity-20 border border-green-200 dark:border-green-800 p-3 text-green-800 dark:text-green-200 transition-colors duration-200"
                        >
                            {{ session('status') }}
                        </div>
                    @endif
                    {{ $slot }}
                </div>
                {{-- Cash Transfer Modal --}}
                <div x-show="cashModal" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center"
                    @keydown.escape.window="cashModal = false">

                    <div class="absolute inset-0 bg-black/45" @click="cashModal = false"></div>

                    <div class="relative bg-white dark:bg-gray-900 w-full max-w-lg mx-4 z-10 rounded-md shadow-xl transition-colors duration-200">

                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Cash Transfer</h2>
                            <button @click="cashModal = false" class="text-xl leading-none text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors duration-200">&times;</button>
                        </div>

                        <form method="POST" action="{{ route('admin.cash-transfer.store') }}">
                            @csrf
                            <div class="px-6 py-5 grid gap-4">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                    <select name="from_company_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                        <option value="">Select Company</option>
                                        @foreach($companies as $co)
                                            <option value="{{ $co->id }}" {{ old('from_company_id') == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_company_id') <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                    <select name="to_company_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                        <option value="">Select Company</option>
                                        @foreach($companies as $co)
                                            <option value="{{ $co->id }}" {{ old('to_company_id') == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_company_id') <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                                        value="{{ old('amount') }}"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                    @error('amount') <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                                    <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                                    @error('date') <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
                                    <textarea name="note" rows="2" placeholder="Optional note..."
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 resize-none transition-colors duration-200">{{ old('note') }}</textarea>
                                    @error('note') <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                            </div>

                            <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                                <button type="button" @click="cashModal = false"
                                    class="px-5 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-5 py-2 text-sm font-medium rounded bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">
                                    Transfer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-app-layout>
