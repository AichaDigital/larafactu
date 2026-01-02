{{--
    Stats Container Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.stats>
        <x-ui.stat title="Downloads" value="31K" />
        <x-ui.stat title="Users" value="4,200" />
    </x-ui.stats>

    <x-ui.stats vertical>
        ...
    </x-ui.stats>
--}}
@props([
    'vertical' => false,
])

@php
    $classes = ['stats', 'shadow', 'bg-base-100'];

    if ($vertical) {
        $classes[] = 'stats-vertical';
    }
@endphp

<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
