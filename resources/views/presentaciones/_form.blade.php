<div class="mb-3">
    <label for="name" class="form-label">Producto</label>
    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $presentation->name ?? 'Sopa Maruchan') }}">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="presentation_type" class="form-label">Tipo de presentación</label>
        <select id="presentation_type" name="presentation_type" class="form-select @error('presentation_type') is-invalid @enderror">
            <option value="">— Selecciona —</option>
            @foreach (['vaso', 'bolsa', 'caja'] as $type)
                <option value="{{ $type }}" @selected(old('presentation_type', $presentation->presentation_type ?? '') === $type)>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
        @error('presentation_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="flavor" class="form-label">Sabor</label>
        <input type="text" id="flavor" name="flavor" class="form-control"
            value="{{ old('flavor', $presentation->flavor ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="pieces_per_box" class="form-label">Piezas por caja</label>
        <input type="number" id="pieces_per_box" name="pieces_per_box" min="0"
            class="form-control @error('pieces_per_box') is-invalid @enderror"
            value="{{ old('pieces_per_box', $presentation->pieces_per_box ?? '') }}">
        @error('pieces_per_box')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="sku" class="form-label">SKU (código QR/barras)</label>
        <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror"
            value="{{ old('sku', $presentation->sku ?? '') }}">
        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
                @checked(old('active', $presentation->active ?? true))>
            <label for="active" class="form-check-label">Activa</label>
        </div>
    </div>
</div>