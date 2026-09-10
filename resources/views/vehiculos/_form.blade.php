<div class="mb-3">
    <label for="code" class="form-label">Código del vehículo</label>
    <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
        value="{{ old('code', $vehicle->code ?? '') }}" placeholder="Ej. VEH-01">
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="plate" class="form-label">Placa</label>
        <input type="text" id="plate" name="plate" class="form-control"
            value="{{ old('plate', $vehicle->plate ?? '') }}" placeholder="Ej. ABC-123">
    </div>
    <div class="col-md-6 mb-3">
        <label for="capacity" class="form-label">Capacidad (unidades)</label>
        <input type="number" id="capacity" name="capacity" min="0" step="0.01"
            class="form-control @error('capacity') is-invalid @enderror"
            value="{{ old('capacity', $vehicle->capacity ?? '') }}">
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="driver_name" class="form-label">Conductor asignado</label>
        <input type="text" id="driver_name" name="driver_name" class="form-control"
            value="{{ old('driver_name', $vehicle->driver_name ?? '') }}">
    </div>
    <div class="col-md-6 mb-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
                @checked(old('active', $vehicle->active ?? true))>
            <label for="active" class="form-check-label">Activo</label>
        </div>
    </div>
</div>