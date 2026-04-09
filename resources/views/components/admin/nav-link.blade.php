@props(['active' => false, 'href' => '#'])

@php
$base = 'w-full inline-flex items-center px-3 py-2 rounded-md transition-colors duration-200';
$classes = $active
    ? $base.' bg-gray-900 dark:bg-indigo-600 text-white dark:text-white'
    : $base.' text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
