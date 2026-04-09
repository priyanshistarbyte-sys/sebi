<x-layouts.admin
    title="Add User"
    :breadcrumbs="[
        ['label'=>'Dashboard','url'=>route('admin.dashboard')],
        ['label'=>'Users','url'=>route('admin.users.index')],
        ['label'=>'Add']
    ]"
>
    <h1 class="text-xl font-semibold mb-4">Add User</h1>
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 max-w-lg">
        @csrf
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input name="name" value="{{ old('name') }}" class="w-full border p-2 rounded">
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input name="email" value="{{ old('email') }}" class="w-full border p-2 rounded">
            @error('email')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Password</label>
            <input type="password" name="password" class="w-full border p-2 rounded">
            @error('password')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
            <span>Admin</span>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Create</button>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
