<div class="mb-3">
    <label for="name" class="form-label">Nombre del cliente</label>
    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $customer->name ?? '') }}">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $customer->email ?? '') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="phone" class="form-label">Teléfono</label>
        <input type="text" id="phone" name="phone" class="form-control"
            value="{{ old('phone', $customer->phone ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Dirección</label>
    <input type="text" id="address" name="address" class="form-control"
        value="{{ old('address', $customer->address ?? '') }}">
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
            @checked(old('active', $customer->active ?? true))>
        <label for="active" class="form-check-label">Activo</label>
    </div>
</div>