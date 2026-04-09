@props(['admin' => false])
<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs
    {{ $admin ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }} transition-colors duration-200">
    @if($admin)
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        Admin
    @else
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
        User
    @endif
</span>
