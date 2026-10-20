{{-- Formulario compartido para crear y editar libros. --}}
{{-- Recibe $categorias y $libro (null al crear). --}}

<label for="titulo">Título *</label>
<input type="text" id="titulo" name="titulo" required maxlength="255"
       value="{{ old('titulo', $libro->titulo ?? '') }}">
@error('titulo')
    <div class="error">{{ $message }}</div>
@enderror

<label for="autor">Autor *</label>
<input type="text" id="autor" name="autor" required maxlength="180"
       value="{{ old('autor', $libro->autor ?? '') }}">
@error('autor')
    <div class="error">{{ $message }}</div>
@enderror

<div class="fila">
    <div>
        <label for="isbn">ISBN</label>
        <input type="text" id="isbn" name="isbn" maxlength="20"
               value="{{ old('isbn', $libro->isbn ?? '') }}">
        @error('isbn')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label for="id_categoria">Categoría</label>
        <select id="id_categoria" name="id_categoria">
            <option value="">Sin categoría</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}"
                    @selected(old('id_categoria', $libro->id_categoria ?? null) == $categoria->id)>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_categoria')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="fila">
    <div>
        <label for="editorial">Editorial</label>
        <input type="text" id="editorial" name="editorial" maxlength="150"
               value="{{ old('editorial', $libro->editorial ?? '') }}">
        @error('editorial')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label for="anio">Año</label>
        <input type="number" id="anio" name="anio" min="1000" max="{{ date('Y') + 1 }}"
               value="{{ old('anio', $libro->anio ?? '') }}">
        @error('anio')
            <div class="error">{{ $message }}</div>
        @enderror
    </div>
</div>

<label for="portada_url">URL de la portada</label>
<input type="text" id="portada_url" name="portada_url" maxlength="500"
       placeholder="https://..."
       value="{{ old('portada_url', $libro->portada_url ?? '') }}">
@error('portada_url')
    <div class="error">{{ $message }}</div>
@enderror

<label for="sinopsis">Sinopsis</label>
<textarea id="sinopsis" name="sinopsis" rows="4">{{ old('sinopsis', $libro->sinopsis ?? '') }}</textarea>
@error('sinopsis')
    <div class="error">{{ $message }}</div>
@enderror

<label for="stock_total">Stock total *</label>
<input type="number" id="stock_total" name="stock_total" required min="0"
       value="{{ old('stock_total', $libro->stock_total ?? '') }}">
@error('stock_total')
    <div class="error">{{ $message }}</div>
@enderror

@isset($libro)
    <p><em>Disponibles ahora: {{ $libro->stock_disponible }} de {{ $libro->stock_total }} ejemplares.</em></p>
@endisset
