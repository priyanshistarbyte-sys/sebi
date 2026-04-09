{{-- resources/views/layouts/admin.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto p-6">
        {{-- flash messages --}}
        @if(session('status'))
            <div class="mb-4 rounded bg-green-100 dark:bg-green-900 p-3 text-green-800 dark:text-green-200 transition-colors duration-200">
                {{ session('status') }}
            </div>
        @endif

        {{-- page content --}}
        {{ $slot }}
    </div>
</x-app-layout>
