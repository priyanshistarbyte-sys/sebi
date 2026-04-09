@props(['name', 'value' => null, 'checked' => false, 'label' => null])

<div class="flex items-center gap-2">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value ?? 1 }}"
        {{ $checked ? 'checked' : '' }}
        class="w-4 h-4 text-blue-600 dark:text-blue-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-2 transition-colors duration-200"
        {{ $attributes->merge(['class' => '']) }}
    >
    @if($label)
        <label class="text-sm text-gray-900 dark:text-gray-100">{{ $label }}</label>
    @endif
</div>