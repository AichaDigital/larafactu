{{--
    Loading Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.loading />
    <x-ui.loading type="spinner" size="lg" color="primary" />
    <x-ui.loading type="dots" />
--}}
@props([
    'type' => 'spinner',
    'size' => null,
    'color' => null,
])

@php
    $classes = ['loading', "loading-{$type}"];

    // Size
    if ($size) {
        $classes[] = "loading-{$size}";
    }

    // Color
    if ($color) {
        $classes[] = "text-{$color}";
    }
@endphp

<span {{ $attributes->class($classes) }}></span>
