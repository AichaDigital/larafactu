{{--
    Table Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.table :headers="['ID', 'Nombre', 'Email', 'Acciones']">
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <x-ui.button size="sm">Editar</x-ui.button>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-ui.table :headers="$headers" zebra hover pin-rows>
        {{ $slot }}
    </x-ui.table>
--}}
@props([
    'headers' => [],
    'zebra' => false,
    'hover' => false,
    'pinRows' => false,
    'pinCols' => false,
    'size' => null,
])

@php
    $tableClasses = ['table'];

    // Modifiers
    if ($zebra) $tableClasses[] = 'table-zebra';
    if ($pinRows) $tableClasses[] = 'table-pin-rows';
    if ($pinCols) $tableClasses[] = 'table-pin-cols';

    // Size
    if ($size) {
        $tableClasses[] = "table-{$size}";
    }
@endphp

<div class="overflow-x-auto">
    <table {{ $attributes->class($tableClasses) }}>
        @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody @if($hover) class="[&>tr:hover]:bg-base-200" @endif>
            {{ $slot }}
        </tbody>
    </table>
</div>
