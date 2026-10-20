{{-- Formulario compartido para crear y editar categorías. --}}
{{-- Recibe $categoria (null al crear). --}}

<label for="nombre">Nombre *</label>
<input type="text" id="nombre" name="nombre" required maxlength="100"
       value="{{ old('nombre', $categoria->nombre ?? '') }}">
@error('nombre')
    <div class="error">{{ $message }}</div>
@enderror

<label for="descripcion">Descripción</label>
<textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
@error('descripcion')
    <div class="error">{{ $message }}</div>
@enderror
