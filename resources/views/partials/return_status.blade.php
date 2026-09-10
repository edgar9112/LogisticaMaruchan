@php
    $config = match ($status ?? '') {
        'SOLICITADA' => ['class' => 'bg-warning text-dark', 'pulse' => true],
        'EN_RUTA_DEVOLUCION' => ['class' => 'bg-info', 'pulse' => true],
        'RECIBIDA_ALMACEN' => ['class' => 'bg-success', 'pulse' => false],
        default => ['class' => 'bg-secondary', 'pulse' => false],
    };
    $label = \App\Models\OrderReturn::STATUS_LABELS[$status ?? ''] ?? str_replace('_', ' ', $status ?? '');
    $pulseClass = $config['pulse'] ? 'badge-pulse' : '';
@endphp
<span class="badge {{ $config['class'] }} {{ $pulseClass }}">{{ $label }}</span>
