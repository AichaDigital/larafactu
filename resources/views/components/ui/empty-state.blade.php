{{--
    Empty State Component - Custom component
    ADR-005: Built with DaisyUI classes

    Usage:
    <x-ui.empty-state
        title="No hay facturas"
        description="Crea tu primera factura para empezar"
        action-label="Nueva factura"
        action-href="/invoices/create"
    />
--}}
@props([
    'title',
    'description' => null,
    'icon' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->class(['text-center', 'py-12']) }}>
    @if($icon)
        <div class="mx-auto mb-4 text-base-content/30">
            {{ $icon }}
        </div>
    @else
        <div class="mx-auto mb-4 text-base-content/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
    @endif

    <h3 class="text-lg font-medium text-base-content">{{ $title }}</h3>

    @if($description)
        <p class="mt-2 text-sm text-base-content/60 max-w-sm mx-auto">{{ $description }}</p>
    @endif

    @if($actionLabel && $actionHref)
        <div class="mt-6">
            <a href="{{ $actionHref }}" class="btn btn-primary">
                {{ $actionLabel }}
            </a>
        </div>
    @endif

    {{ $slot }}
</div>
