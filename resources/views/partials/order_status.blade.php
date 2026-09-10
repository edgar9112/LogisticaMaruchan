@php
    $config = match ($status ?? '') {
        'CREADO' => ['class' => 'bg-primary', 'pulse' => false],
        'CONFIRMADO' => ['class' => 'bg-info', 'pulse' => false],
        'EN_ALMACEN' => ['class' => 'bg-info', 'pulse' => false],
        'RECIBIDO' => ['class' => 'bg-info', 'pulse' => false],
        'CLASIFICADO' => ['class' => 'bg-secondary', 'pulse' => false],
        'PREPARADO' => ['class' => 'bg-secondary', 'pulse' => false],
        'ASIGNADO_EMBARQUE' => ['class' => 'bg-warning text-dark', 'pulse' => true],
        'CARGADO' => ['class' => 'bg-warning text-dark', 'pulse' => true],
        'EN_TRANSITO' => ['class' => 'text-bg-primary', 'pulse' => true],
        'RECIBIDO_TIENDA' => ['class' => 'bg-success', 'pulse' => false],
        'CERRADO' => ['class' => 'bg-success', 'pulse' => false],
        default => ['class' => 'bg-secondary', 'pulse' => false],
    };
    $label = str_replace('_', ' ', $status ?? '');
    $pulseClass = $config['pulse'] ? 'badge-pulse' : '';
@endphp
<span class="badge {{ $config['class'] }} {{ $pulseClass }}">{{ $label }}</span>
