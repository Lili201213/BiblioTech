{{-- Mensajes flash (éxito y error) que se muestran arriba del contenido --}}

@if (session('exito'))
    <div class="ok">{{ session('exito') }}</div>
@endif

@if (session('error'))
    <div class="error">{{ session('error') }}</div>
@endif

{{-- Errores de validación de cada campo se muestran junto al campo (directiva @error) --}}
