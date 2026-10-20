{{-- Banner reutilizable (panel y otras pantallas). --}}
{{-- Parámetros: titulo (texto), subtitulo (texto), compacto (true = sin estante) --}}
@props(['titulo', 'subtitulo' => '', 'compacto' => false])

<section class="banner{{ $compacto ? ' banner-compacto' : '' }}">
    <div class="banner-texto">
        {{-- Etiqueta pequeña con la fecha de hoy en español --}}
        <p class="banner-fecha">
            {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
        </p>

        <h1 class="banner-titulo">{{ $titulo }}</h1>

        @if ($subtitulo !== '')
            <p class="banner-subtitulo">{{ $subtitulo }}</p>
        @endif

        {{-- Aquí entran los botones (o el buscador) de cada pantalla --}}
        <div class="banner-acciones">
            {{ $slot }}
        </div>
    </div>

    @unless ($compacto)
        {{-- Este decorativo: solo CSS, sin imágenes. Se oculta bajo 900px. --}}
        <div class="banner-estante" aria-hidden="true">
            <div class="banner-libros">
                <span class="banner-libro banner-libro-1"></span>
                <span class="banner-libro banner-libro-2"></span>
                <span class="banner-libro banner-libro-3 inclinado-a"></span>
                <span class="banner-libro banner-libro-4"></span>
                <span class="banner-libro banner-libro-5"></span>
                <span class="banner-libro banner-libro-6 inclinado-b"></span>
                <span class="banner-libro banner-libro-7"></span>
                <span class="banner-libro banner-libro-8"></span>
                <span class="banner-libro banner-libro-9 inclinado-c"></span>
                <span class="banner-libro banner-libro-10"></span>
                <span class="banner-libro banner-libro-11"></span>
            </div>
            {{-- Repisa de madera --}}
            <div class="banner-repisa"></div>
        </div>
    @endunless
</section>
