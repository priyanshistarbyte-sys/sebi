@php
    // Auto-open modal if validation failed coming from the modal form
    $openCreate = ($errors->any() && old('_from') === 'createUserModal');
@endphp

<x-layouts.admin
    title="Users"
    :breadcrumbs="[
        ['label'=>'Dashboard','url'=>route('admin.dashboard')],
        ['label'=>'Users']
    ]"
>
    <div x-data="{ openCreate: {{ $openCreate ? 'true' : 'false' }} }" class="space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Users</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.create') }}" class="hidden sm:inline-flex px-3 py-2 border rounded">
                    Open Create Page
                </a>
                <button @click="openCreate = true" class="px-3 py-2 bg-blue-600 text-white rounded">
                    + New User
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="text-left">
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">ID</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Name</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Email</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">Role</th>
                        <th class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-gray-800">
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $u->id }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $u->name }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ $u->email }}</td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                            <x-admin.role-badge :admin="$u->is_admin" />
                        </td>
                        <td class="p-2 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 flex items-center justify-center">
                            <a class="text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200" href="{{ route('admin.users.edit',$u) }}">
                                <svg viewBox="0 0 48 48" class="h-4 w-4"><defs><style>.cls-1{fill:#fc6}.cls-7{fill:#ffba55}</style></defs><g id="pencil"><path class="cls-1" d="M40.58 15.75 12.81 43.53l-1.56-2.59c-.65-1.08-.07-.74-2.61-1.58-.85-2.54-.49-2-1.59-2.61L4.47 35.2 32.25 7.42z"/><path d="M39.58 14.75C18.81 35.52 19 36 15.24 37a10.35 10.35 0 0 1-9.77-2.8L32.25 7.42z" style="fill:#ffde76"/><path d="m12.81 43.53-6 1.75a8.76 8.76 0 0 0-4.12-4.12c.68-2.3.28-.93 1.75-6l3.47 2.08.7 2.08 2.08.7c1.31 2.21.85 1.41 2.12 3.51z" style="fill:#f6ccaf"/><path d="M11.75 41.78c-4.49.81-7.52-1.83-8.52-2.35l1.24-4.23 3.47 2.08.7 2.08 2.08.7z" style="fill:#ffdec7"/><path d="M6.84 45.28c0 .1.09 0-5.84 1.72.81-2.76.42-1.45 1.72-5.84a8.85 8.85 0 0 1 4.12 4.12z" style="fill:#374f68"/><path d="m5.78 43.6-4.14 1.21 1.08-3.65a8.67 8.67 0 0 1 3.06 2.44z" style="fill:#425b72"/><path class="cls-7" d="M38.51 13.68 11.25 40.94c-.64-1.07-.26-.79-1.58-1.24L37.1 12.27zM35.74 10.91 8.3 38.34c-.45-1.33-.17-1-1.25-1.59L34.32 9.49z"/><path class="cls-1" d="M35.74 10.91 9.83 36.81a10.59 10.59 0 0 1-2-.84L34.32 9.49z"/><path d="M46.14 10.2 43.36 13 35 4.64l2.8-2.78a3 3 0 0 1 4.17 0L46.14 6a3 3 0 0 1 0 4.2z" style="fill:#db5669"/><path d="M46.83 7.11c-.77 2.2-4.18 3.15-6.25 1.08L36 3.64l1.8-1.78a3 3 0 0 1 4.17 0c4.61 4.61 4.58 4.45 4.86 5.25z" style="fill:#f26674"/><path d="m43.36 13-2.78 2.78-8.33-8.36L35 4.64z" style="fill:#dad7e5"/><path d="M42.36 12a2.52 2.52 0 0 1-3.56 0l-5.55-5.58L35 4.64z" style="fill:#edebf2"/><path class="cls-1" d="M38.51 13.68 15.24 37a10.69 10.69 0 0 1-3.09.27l25-24.95z"/></g></svg>
                            </a>
                            <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 dark:text-red-400 underline hover:text-red-800 dark:hover:text-red-300 transition-colors duration-200 ml-2">
                                    <svg x="0" y="0" viewBox="0 0 256 256" class="h-5 w-5" style="enable-background:new 0 0 256 256" xml:space="preserve"><style>.st11{fill:#6770e6}.st12{fill:#5861c7}.st16{fill:#858eff}</style><path class="st11" d="M197 70H59c-8.837 0-16 7.163-16 16v14h170V86c0-8.837-7.163-16-16-16z"/><path class="st16" d="M197 70H59c-8.837 0-16 7.164-16 16v6c0-8.836 7.163-16 16-16h138c8.837 0 16 7.164 16 16v-6c0-8.836-7.163-16-16-16z"/><path class="st12" d="M169 70h-12v-4c0-5.514-4.486-10-10-10h-38c-5.514 0-10 4.486-10 10v4H87v-4c0-12.131 9.869-22 22-22h38c12.131 0 22 9.869 22 22v4z"/><path class="st11" d="M147 44h-38c-12.131 0-22 9.869-22 22v4h.095C88.109 58.803 97.544 50 109 50h38c11.456 0 20.891 8.803 21.905 20H169v-4c0-12.131-9.869-22-22-22z"/><path class="st16" d="M215 116H41a8 8 0 0 1 0-16h174a8 8 0 0 1 0 16z"/><path class="st11" d="M213 116H43l18.038 126.263A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737L213 116z"/><path class="st12" d="M179.944 250H76.056c-7.23 0-13.464-4.682-15.527-11.303l.509 3.565A16 16 0 0 0 76.877 256h102.247a16 16 0 0 0 15.839-13.737l.509-3.565c-2.063 6.62-8.297 11.302-15.528 11.302zM82.665 136h-.93c-4.141 0-7.377 3.576-6.965 7.697l8.6 86A7 7 0 0 0 90.335 236h.93c4.141 0 7.377-3.576 6.965-7.697l-8.6-86A7 7 0 0 0 82.665 136zM165.165 236h-.93c-4.141 0-7.377-3.576-6.965-7.697l8.6-86a7 7 0 0 1 6.965-6.303h.93c4.141 0 7.377 3.576 6.965 7.697l-8.6 86a7 7 0 0 1-6.965 6.303zM128.5 136h-1a7 7 0 0 0-7 7v86a7 7 0 0 0 7 7h1a7 7 0 0 0 7-7v-86a7 7 0 0 0-7-7z"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $users->links() }}</div>

        {{-- CREATE USER MODAL --}}
        <div
            x-show="openCreate"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/40 dark:bg-black/60 transition-colors duration-200"
            @click.self="openCreate = false"
            @keydown.escape.window="openCreate=false"
        ></div>

        <div
            x-show="openCreate"
            x-transition
            class="fixed z-50 inset-0 grid place-items-center p-4"
            aria-modal="true" role="dialog"
        >
            <div class="w-full max-w-lg rounded-lg bg-white dark:bg-gray-900 shadow-lg dark:shadow-gray-800 transition-colors duration-200">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Create User</h2>
                    <button class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200" @click="openCreate=false">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="p-4 space-y-4">
                    @csrf
                    <input type="hidden" name="_from" value="createUserModal">

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Name</label>
                        <input name="name" value="{{ old('name') }}" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                        @error('name')<p class="text-red-600 dark:text-red-400 text-xs mt-1 transition-colors duration-200">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Email</label>
                        <input name="email" value="{{ old('email') }}" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                        @error('email')<p class="text-red-600 dark:text-red-400 text-xs mt-1 transition-colors duration-200">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Password</label>
                        <input type="password" name="password" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                        @error('password')<p class="text-red-600 dark:text-red-400 text-xs mt-1 transition-colors duration-200">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }} class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-200">
                        <span class="text-gray-900 dark:text-gray-100">Admin</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" @click="openCreate=false">Cancel</button>
                        <button class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">Create</button>
                    </div>
                </form>
            </div>
        </div>
        {{-- /MODAL --}}
    </div>
</x-layouts.admin>
