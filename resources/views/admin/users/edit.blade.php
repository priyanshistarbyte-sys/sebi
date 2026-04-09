<x-layouts.admin
    title="Edit User"
    :breadcrumbs="[
        ['label'=>'Dashboard','url'=>route('admin.dashboard')],
        ['label'=>'Users','url'=>route('admin.users.index')],
        ['label'=>'Edit']
    ]"
>
    <h1 class="text-xl font-semibold mb-4">Edit User</h1>
    <form method="POST" action="{{ route('admin.users.update',$user) }}" class="space-y-4 max-w-lg">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input name="name" value="{{ old('name',$user->name) }}" class="w-full border p-2 rounded">
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input name="email" value="{{ old('email',$user->email) }}" class="w-full border p-2 rounded">
            @error('email')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Password (leave blank to keep)</label>
            <input type="password" name="password" class="w-full border p-2 rounded">
            @error('password')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_admin" value="1" {{ old('is_admin',$user->is_admin) ? 'checked' : '' }}>
            <span>Admin</span>
        </div>
        @php($allCompanies = \App\Models\Company::orderBy('name')->get())
        <div>
            <label class="block text-sm font-medium mb-1">Assigned Companies</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 border rounded p-3 max-h-60 overflow-auto">
                @foreach($allCompanies as $co)
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="companies[]"
                            value="{{ $co->id }}"
                            {{ in_array($co->id, old('companies', $user->companies()->pluck('companies.id')->toArray())) ? 'checked' : '' }}>
                        <span>{{ $co->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('companies')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
