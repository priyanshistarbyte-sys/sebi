<nav class="h-full p-4 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">Control Panel</a>
    </div>

    <ul class="space-y-1 text-sm">
        <li>
            <x-admin.nav-link
                :href="route('admin.dashboard')"
                :active="request()->routeIs('dashboard')"
            >
                <svg class="h-4 w-4 mr-2 text-gray-600 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
                Dashboard
            </x-admin.nav-link>
        </li>
        <li>
            <x-admin.nav-link
                :href="route('admin.users.index')"
                :active="request()->routeIs('admin.users.*')"
            >
                <svg class="h-4 w-4 mr-2 text-gray-600 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path xmlns="http://www.w3.org/2000/svg" d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Users
            </x-admin.nav-link>
        </li>
        <li>
            <x-admin.nav-link
                :href="route('admin.companies.index')"
                :active="request()->routeIs('admin.companies.*')"
            >
                <svg class="h-4 w-4 mr-2 text-gray-600 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M11 20H21V10C21 8.89543 20.1046 8 19 8H15M11 16H11.01M17 16H17.01M7 16H7.01M11 12H11.01M17 12H17.01M7 12H7.01M11 8H11.01M7 8H7.01M15 20V6C15 4.89543 14.1046 4 13 4H5C3.89543 4 3 4.89543 3 6V20H15Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Companies
            </x-admin.nav-link>
        </li>
        <li>
            <x-admin.nav-link
                :href="route('admin.categories.index')"
                :active="request()->routeIs('admin.categories.*')"
            >
                <svg class="h-4 w-4 mr-2 text-gray-600 dark:text-gray-400" viewBox="0 0 48 48" fill="currentColor" stroke="none">
                    <g id="Layer_2" data-name="Layer 2">
                        <g id="icons_Q2" data-name="icons Q2">
                        <path d="M24,2a2.1,2.1,0,0,0-1.7,1L13.2,17a2.3,2.3,0,0,0,0,2,1.9,1.9,0,0,0,1.7,1H33a2.1,2.1,0,0,0,1.7-1,1.8,1.8,0,0,0,0-2l-9-14A1.9,1.9,0,0,0,24,2Z"/>
                        <path d="M43,43H29a2,2,0,0,1-2-2V27a2,2,0,0,1,2-2H43a2,2,0,0,1,2,2V41A2,2,0,0,1,43,43Z"/>
                        <path d="M13,24A10,10,0,1,0,23,34,10,10,0,0,0,13,24Z"/>
                        </g>
                    </g>
                </svg>
                Categories
            </x-admin.nav-link>
        </li>
        <li>
            <x-admin.nav-link
                :href="route('admin.transactions.index')"
                :active="request()->routeIs('admin.transactions.*')"
            >
                <svg class="h-4 w-4 mr-2 text-gray-600 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path xmlns="http://www.w3.org/2000/svg" d="M6 4H10.5M10.5 4C12.9853 4 15 6.01472 15 8.5C15 10.9853 12.9853 13 10.5 13H6L13 20M10.5 4H18M6 8.5H18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Transactions
            </x-admin.nav-link>
        </li>
    </ul>
</nav>
