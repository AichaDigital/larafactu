{{--
    Stat Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.stat title="Total facturas" value="31,450 EUR" description="+12% desde el mes pasado" />
    <x-ui.stat title="Clientes" value="256" color="primary">
        <x-slot:icon>
            <svg>...</svg>
        </x-slot:icon>
    </x-ui.stat>
--}}
@props([
    'title',
    'value',
    'description' => null,
    'icon' => null,
    'figure' => null,
])

<div class="stat">
    @if($figure)
        <div class="stat-figure">
            {{ $figure }}
        </div>
    @elseif($icon)
        <div class="stat-figure text-primary">
            {{ $icon }}
        </div>
    @endif

    <div class="stat-title">{{ $title }}</div>
    <div class="stat-value">{{ $value }}</div>

    @if($description)
        <div class="stat-desc">{{ $description }}</div>
    @endif
</div>
