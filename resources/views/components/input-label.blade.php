@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-gray-300 transition-colors duration-200']) }}>
    {{ $value ?? $slot }}
</label>
