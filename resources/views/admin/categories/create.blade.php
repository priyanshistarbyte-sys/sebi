<x-layouts.admin title="Add Category" :breadcrumbs="[
    ['label'=>'Dashboard','url'=>route('admin.dashboard')],
    ['label'=>'Categories','url'=>route('admin.categories.index')],
    ['label'=>'Add']
]">
    @if(!$canCreate)
        <div class="mb-3 rounded border border-amber-200 bg-amber-50 p-3 text-amber-800">
            Select a company in the header to enable the form.
        </div>
    @else
        <div class="mb-3 text-sm text-gray-600">
            Company: <strong>{{ $activeCompany->name }}</strong>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4 max-w-lg">
        @csrf

        <div>
            <label class="block text-sm font-medium">Type</label>
            <select name="type" id="type" class="w-full border p-2 rounded" {{ $canCreate ? '' : 'disabled' }}>
                @foreach(['Income/Expense','Account'] as $t)
                    <option value="{{ $t }}" {{ old('type')===$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>
            @error('type')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Name</label>
            <input name="name" value="{{ old('name') }}" class="w-full border p-2 rounded" {{ $canCreate ? '' : 'disabled' }}>
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
            @error('company')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4 rounded border p-3 bg-gray-50 text-blue-600 dark:text-blue-500 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-2 transition-colors duration-200" id="sod_option">
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="on_dashboard" id="on_dashboard_checkbox" value="1">
                    <span class="ml-2 text-sm">Show On Dashboard?</span>
                </label>
            </div>
            <!-- Radio buttons (hidden initially) -->
            <div id="dashboard_options" class="mt-3 hidden">
                <x-radio name="dashboard_period" value="monthly" label="Monthly" class="mr-4" />
                <x-radio name="dashboard_period" value="all_time" label="All Time" />
            </div>
        </div>

        <div class="mt-4 rounded border p-3 bg-gray-50 hidden" id="">
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_default" id="is_default" value="1">
                    <span class="ml-2 text-sm">Manage Opening Balance</span>
                </label>
            </div>
        </div>

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded" {{ $canCreate ? '' : 'disabled' }}>
                Create
            </button>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 border rounded">Cancel</a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkbox = document.getElementById('on_dashboard_checkbox');
            const options  = document.getElementById('dashboard_options');
            const monthlyoptions  = document.getElementById('monthly');
            if (!checkbox || !options) return;

            const radios = options.querySelectorAll('input[type="radio"]');
            const toggle = () => {
                if (checkbox.checked) {
                    options.classList.remove('hidden');
                    radios.forEach(r => r.required = true);
                    monthlyoptions.checked = true;
                } else {
                    options.classList.add('hidden');
                    radios.forEach(r => { r.required = false; r.checked = false; });
                }
            };

            // initial state on load (handles old() repopulation too)
            toggle();
            checkbox.addEventListener('change', toggle);
            
            const type  = document.getElementById('type');
            const sod_option  = document.getElementById('sod_option');
            const changeType = () => {
                if (type.value == 'Account') {
                    sod_option.classList.add('hidden');
                    checkbox.checked = false;
                    toggle();
                } else {
                    sod_option.classList.remove('hidden');
                    checkbox.checked = false;
                    toggle();
                }
            };
            changeType();
            type.addEventListener('change', changeType);
        });
    </script>
</x-layouts.admin>
