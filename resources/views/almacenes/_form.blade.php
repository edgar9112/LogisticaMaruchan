<div class="row">
    <div class="col-md-6 mb-3">
        <label for="code" class="form-label">Código</label>
        <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $warehouse->code ?? '') }}">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $warehouse->name ?? '') }}">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Dirección</label>
    <input type="text" id="address" name="address" class="form-control"
        value="{{ old('address', $warehouse->address ?? '') }}">
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
            @checked(old('active', $warehouse->active ?? true))>
        <label for="active" class="form-check-label">Activo</label>
    </div>
</div>