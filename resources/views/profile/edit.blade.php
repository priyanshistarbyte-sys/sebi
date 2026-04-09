<x-layouts.admin title="Profile" :breadcrumbs="[
    ['label'=>'Dashboard','url'=>route('admin.dashboard')],
    ['label'=>'Profile']
]">
    <div class="space-y-6">
        @if(session('status'))
            <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-700 text-green-900 dark:text-green-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-900 shadow dark:shadow-gray-800 sm:rounded-lg transition-colors duration-200">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-900 shadow dark:shadow-gray-800 sm:rounded-lg transition-colors duration-200">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        @php
            $activePeriod = session('active_period') ?: now()->format('Y-m');
            try {
                $activeMonth = \Carbon\Carbon::createFromFormat('Y-m', $activePeriod);
            } catch (\Throwable $e) {
                $activeMonth = now()->startOfMonth();
            }
        @endphp
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-900 shadow dark:shadow-gray-800 sm:rounded-lg transition-colors duration-200">
            <div class="max-w-xl">
                <div class="space-y-4">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Opening Balance</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create current month opening balance based on the previous month for the active session period: <strong>{{ $activeMonth->isoFormat('MMMM YYYY') }}</strong>.</p>
                    </div>
                    <form method="POST" action="{{ route('profile.opening-balance') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400 transition">
                            Generate opening balance for {{ $activeMonth->isoFormat('MMMM YYYY') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-900 shadow dark:shadow-gray-800 sm:rounded-lg transition-colors duration-200">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-layouts.admin>
