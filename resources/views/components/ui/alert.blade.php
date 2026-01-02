{{--
    Alert Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.alert>Default alert</x-ui.alert>
    <x-ui.alert color="success">Success!</x-ui.alert>
    <x-ui.alert color="error" style="outline">Error outline</x-ui.alert>
    <x-ui.alert color="warning" :dismissible="true">Warning with close</x-ui.alert>
--}}
@props([
    'color' => null,
    'style' => null,
    'icon' => null,
    'title' => null,
    'dismissible' => false,
])

@php
    $classes = ['alert'];

    // Color
    if ($color) {
        $classes[] = "alert-{$color}";
    }

    // Style
    if ($style) {
        $classes[] = "alert-{$style}";
    }

    // Icon based on color if not provided
    $defaultIcons = [
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    ];
@endphp

<div role="alert" {{ $attributes->class($classes) }}>
    @if($icon)
        {{ $icon }}
    @elseif($color && isset($defaultIcons[$color]))
        <svg xmlns="http://www.w3.org/2000/svg" class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            {!! $defaultIcons[$color] !!}
        </svg>
    @endif

    <div>
        @if($title)
            <h3 class="font-bold">{{ $title }}</h3>
        @endif
        <div class="text-sm">{{ $slot }}</div>
    </div>

    @if($dismissible)
        <button type="button" class="btn btn-sm btn-ghost btn-circle" onclick="this.parentElement.remove()">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
