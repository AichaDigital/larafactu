{{--
    Modal Component - DaisyUI wrapper (dialog element)
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.modal id="my-modal">
        <x-slot:title>Modal Title</x-slot:title>
        Content here
        <x-slot:actions>
            <x-ui.button onclick="document.getElementById('my-modal').close()">Close</x-ui.button>
        </x-slot:actions>
    </x-ui.modal>

    <!-- Open with: document.getElementById('my-modal').showModal() -->
--}}
@props([
    'id',
    'title' => null,
    'actions' => null,
    'closeOnBackdrop' => true,
    'closeButton' => false,
    'position' => null,
])

@php
    $classes = ['modal'];

    // Position
    if ($position) {
        $classes[] = "modal-{$position}";
    }
@endphp

<dialog id="{{ $id }}" {{ $attributes->class($classes) }}>
    <div class="modal-box">
        @if($closeButton)
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>
        @endif

        @if($title)
            <h3 class="text-lg font-bold">{{ $title }}</h3>
        @endif

        <div class="py-4">
            {{ $slot }}
        </div>

        @if($actions)
            <div class="modal-action">
                {{ $actions }}
            </div>
        @endif
    </div>

    @if($closeOnBackdrop)
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    @endif
</dialog>
