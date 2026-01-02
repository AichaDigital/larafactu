{{--
    Card Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.card>
        <x-slot:title>Card Title</x-slot:title>
        Content here
        <x-slot:actions>
            <x-ui.button>Action</x-ui.button>
        </x-slot:actions>
    </x-ui.card>

    <x-ui.card style="border" size="sm">Compact card</x-ui.card>
--}}
@props([
    'title' => null,
    'actions' => null,
    'style' => null,
    'size' => null,
    'side' => false,
    'imageFull' => false,
    'image' => null,
    'imageAlt' => '',
])

@php
    $classes = ['card', 'bg-base-100'];

    // Style
    if ($style) {
        $classes[] = "card-{$style}";
    }

    // Size
    if ($size) {
        $classes[] = "card-{$size}";
    }

    // Modifiers
    if ($side) $classes[] = 'card-side';
    if ($imageFull) $classes[] = 'image-full';
@endphp

<div {{ $attributes->class($classes) }}>
    @if($image)
        <figure>
            <img src="{{ $image }}" alt="{{ $imageAlt }}" />
        </figure>
    @endif

    <div class="card-body">
        @if($title)
            <h2 class="card-title">{{ $title }}</h2>
        @endif

        {{ $slot }}

        @if($actions)
            <div class="card-actions justify-end">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
