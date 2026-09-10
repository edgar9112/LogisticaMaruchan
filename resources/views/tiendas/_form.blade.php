<div class="row">
    <div class="col-md-6 mb-3">
        <label for="code" class="form-label">Código</label>
        <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $store->code ?? '') }}">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $store->name ?? '') }}">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="address" class="form-label">Dirección</label>
        <input type="text" id="address" name="address" class="form-control"
            value="{{ old('address', $store->address ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="phone" class="form-label">Teléfono</label>
        <input type="text" id="phone" name="phone" class="form-control"
            value="{{ old('phone', $store->phone ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="contact_name" class="form-label">Persona de contacto</label>
        <input type="text" id="contact_name" name="contact_name" class="form-control"
            value="{{ old('contact_name', $store->contact_name ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="opening_hours" class="form-label">Horario de atención</label>
        <input type="text" id="opening_hours" name="opening_hours" class="form-control"
            value="{{ old('opening_hours', $store->opening_hours ?? '') }}" placeholder="Ej. Lun–Sáb 9:00–18:00">
    </div>
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
            @checked(old('active', $store->active ?? true))>
        <label for="active" class="form-check-label">Activa</label>
    </div>
</div>