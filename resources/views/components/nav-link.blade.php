<!-- resources/views/components/nav-link.blade.php -->
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center text-sm font-bold transition-all duration-200 relative after:content-[""] after:absolute after:-bottom-2 after:left-0 after:w-full after:h-0.5 after:bg-orange-500 after:rounded-full'
            : 'inline-flex items-center text-sm font-medium text-gray-600 hover:text-orange-600 transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} style="{{ $active ? 'color: var(--rf-primary);' : '' }}">
    {{ $slot }}
</a>