<x-layouts.admin title="Categories" :breadcrumbs="[
    ['label'=>'Dashboard','url'=>route('admin.dashboard')],
    ['label'=>'Categories']
]">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Categories</h1>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.create') }}" class="px-3 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">
                + Add Category
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.categories.index') }}" class="flex items-center gap-3 mb-3">
        {{-- Type filter --}}
        <select id="type" name="type" class="border border-gray-300 dark:border-gray-600 rounded p-2 text-sm w-32 md:w-48 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200" onchange="this.form.submit()">
            <option value="" {{ $currentType ? '' : 'selected' }}>All Type</option>
            @foreach($types as $t)
                <option value="{{ $t }}" {{ $currentType === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>

        @if($currentType || ($isAdmin && $currentCompanyId !== 'all'))
            <a href="{{ route('admin.categories.index') }}" class="text-sm underline text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200">Reset</a>
        @endif
    </form>

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
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('#', 'on_dashboard', $sort, $dir) !!}</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('Type', 'type', $sort, $dir) !!}</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{!! sort_link('Name', 'name', $sort, $dir) !!}</th>
                    <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $c)
                <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $c->id }}
                        @if($c->on_dashboard)
                            <span x-data="{ showTooltip: false }" class="relative inline-block">
                                <span @mouseover="showTooltip = true" @mouseleave="showTooltip = false" class="inline-flex ml-2 rounded-full bg-emerald-100 dark:bg-emerald-900 dark:bg-opacity-30 p-1.5 cursor-pointer transition-colors duration-200"><span class="rounded-full bg-emerald-500 dark:bg-emerald-400 p-1"></span></span>
                                <span x-show="showTooltip"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform scale-90"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-90" 
                                        class="absolute z-10 px-3 py-2 text-sm font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-xs dark:shadow-gray-800 break-keep w-max transition-colors duration-200">
                                    @if($c->dashboard_period == 'monthly')
                                        Show Monthly
                                    @else
                                        Show All Time
                                    @endif
                                    <span class="tooltip-arrow" data-popper-arrow></span>
                                </span>
                            </span>    
                        @endif
                    </td>
                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $c->type }}</td>
                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $c->name }}</td>
                    <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 flex items-center justify-center">
                        <a class="text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200" href="{{ route('admin.categories.edit',$c) }}">
                            <svg viewBox="0 0 48 48" class="h-4 w-4"><defs><style>.cls-1{fill:#fc6}.cls-7{fill:#ffba55}</style></defs><g id="pencil"><path class="cls-1" d="M40.58 15.75 12.81 43.53l-1.56-2.59c-.65-1.08-.07-.74-2.61-1.58-.85-2.54-.49-2-1.59-2.61L4.47 35.2 32.25 7.42z"/><path d="M39.58 14.75C18.81 35.52 19 36 15.24 37a10.35 10.35 0 0 1-9.77-2.8L32.25 7.42z" style="fill:#ffde76"/><path d="m12.81 43.53-6 1.75a8.76 8.76 0 0 0-4.12-4.12c.68-2.3.28-.93 1.75-6l3.47 2.08.7 2.08 2.08.7c1.31 2.21.85 1.41 2.12 3.51z" style="fill:#f6ccaf"/><path d="M11.75 41.78c-4.49.81-7.52-1.83-8.52-2.35l1.24-4.23 3.47 2.08.7 2.08 2.08.7z" style="fill:#ffdec7"/><path d="M6.84 45.28c0 .1.09 0-5.84 1.72.81-2.76.42-1.45 1.72-5.84a8.85 8.85 0 0 1 4.12 4.12z" style="fill:#374f68"/><path d="m5.78 43.6-4.14 1.21 1.08-3.65a8.67 8.67 0 0 1 3.06 2.44z" style="fill:#425b72"/><path class="cls-7" d="M38.51 13.68 11.25 40.94c-.64-1.07-.26-.79-1.58-1.24L37.1 12.27zM35.74 10.91 8.3 38.34c-.45-1.33-.17-1-1.25-1.59L34.32 9.49z"/><path class="cls-1" d="M35.74 10.91 9.83 36.81a10.59 10.59 0 0 1-2-.84L34.32 9.49z"/><path d="M46.14 10.2 43.36 13 35 4.64l2.8-2.78a3 3 0 0 1 4.17 0L46.14 6a3 3 0 0 1 0 4.2z" style="fill:#db5669"/><path d="M46.83 7.11c-.77 2.2-4.18 3.15-6.25 1.08L36 3.64l1.8-1.78a3 3 0 0 1 4.17 0c4.61 4.61 4.58 4.45 4.86 5.25z" style="fill:#f26674"/><path d="m43.36 13-2.78 2.78-8.33-8.36L35 4.64z" style="fill:#dad7e5"/><path d="M42.36 12a2.52 2.52 0 0 1-3.56 0l-5.55-5.58L35 4.64z" style="fill:#edebf2"/><path class="cls-1" d="M38.51 13.68 15.24 37a10.69 10.69 0 0 1-3.09.27l25-24.95z"/></g></svg>
                        </a>
                        <form action="{{ route('admin.categories.destroy',$c) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 dark:text-red-400 underline hover:text-red-800 dark:hover:text-red-300 transition-colors duration-200 ml-2">
                                <svg x="0" y="0" viewBox="0 0 256 256" class="h-5 w-5" style="enable-background:new 0 0 256 256" xml:space="preserve"><style>.st11{fill:#6770e6}.st12{fill:#5861c7}.st16{fill:#858eff}</style><path class="st11" d="M197 70H59c-8.837 0-16 7.163-16 16v14h170V86c0-8.837-7.163-16-16-16z"/><path class="st16" d="M197 70H59c-8.837 0-16 7.164-16 16v6c0-8.836 7.163-16 16-16h138c8.837 0 16 7.164 16 16v-6c0-8.836-7.163-16-16-16z"/><path class="st12" d="M169 70h-12v-4c0-5.514-4.486-10-10-10h-38c-5.514 0-10 4.486-10 10v4H87v-4c0-12.131 9.869-22 22-22h38c12.131 0 22 9.869 22 22v4z"/><path class="st11" d="M147 44h-38c-12.131 0-22 9.869-22 22v4h.095C88.109 58.803 97.544 50 109 50h38c11.456 0 20.891 8.803 21.905 20H169v-4c0-12.131-9.869-22-22-22z"/><path class="st16" d="M215 116H41a8 8 0 0 1 0-16h174a8 8 0 0 1 0 16z"/><path class="st11" d="M213 116H43l18.038 126.263A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737L213 116z"/><path class="st12" d="M179.944 250H76.056c-7.23 0-13.464-4.682-15.527-11.303l.509 3.565A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737l.509-3.565c-2.063 6.62-8.297 11.302-15.528 11.302zM82.665 136h-.93c-4.141 0-7.377 3.576-6.965 7.697l8.6 86A7 7 0 0 0 90.335 236h.93c4.141 0 7.377-3.576 6.965-7.697l-8.6-86A7 7 0 0 0 82.665 136zM165.165 236h-.93c-4.141 0-7.377-3.576-6.965-7.697l8.6-86a7 7 0 0 1 6.965-6.303h.93c4.141 0 7.377 3.576 6.965 7.697l-8.6 86a7 7 0 0 1-6.965 6.303zM128.5 136h-1a7 7 0 0 0-7 7v86a7 7 0 0 0 7 7h1a7 7 0 0 0 7-7v-86a7 7 0 0 0-7-7z"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $isAdmin ? 5 : 4 }}" class="p-4 text-center text-gray-500 dark:text-gray-400 odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">No categories found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
</x-layouts.admin>
