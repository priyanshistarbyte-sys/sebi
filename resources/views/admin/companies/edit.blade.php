<x-layouts.admin title="Edit Company" :breadcrumbs="[
    ['label'=>'Dashboard','url'=>route('admin.dashboard')],
    ['label'=>'Companies','url'=>route('admin.companies.index')],
    ['label'=>'Edit']
]">
    <form method="POST" action="{{ route('admin.companies.update',$company) }}" class="space-y-4 max-w-lg">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input name="name" value="{{ old('name',$company->name) }}" class="w-full border p-2 rounded">
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Currency</label>
            <select name="currency_symbol" class="w-full border p-2 rounded">
                @foreach(['₹','$', '€','£'] as $sym)
                    <option value="{{ $sym }}" {{ old('currency_symbol',$company->currency_symbol)===$sym?'selected':'' }}>
                        {{ $sym }}
                    </option>
                @endforeach
            </select>
            @error('currency_symbol')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 border rounded">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
