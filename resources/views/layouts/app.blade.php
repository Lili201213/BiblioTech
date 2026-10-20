{{-- Layout principal de BiblioTech. Todas las páginas heredan de esta plantilla. --}}
{{-- Estructura: barra superior (todos los roles) + menú lateral (solo gestores) + contenido --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Inicio') - BiblioTech</title>
    {{-- Hoja de estilos del proyecto (sin Bootstrap ni Vite) --}}
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>

@php
    // Usuario de la sesión (null si es un invitado en la página de login)
    $usuarioSesion = auth()->user();
    $esGestor = $usuarioSesion && $usuarioSesion->esGestor();
@endphp

{{-- Barra superior morada con el canto dorado de estante --}}
<nav class="nav">
    <div>
        {{-- El logo lleva al panel de bienvenida --}}
        <a href="{{ route('panel') }}" class="marca">📚 BiblioTech</a>

        @auth
            {{-- "Inicio" solo para quien no tiene menú lateral (los gestores
                 ya tienen "Panel" en el menú lateral: cada enlace, una sola vez) --}}
            @unless ($esGestor)
                <a href="{{ route('panel') }}">Inicio</a>
            @endunless

            <a href="{{ route('catalogo') }}">Catálogo</a>

            {{-- Enlaces propios del estudiante --}}
            @unless ($esGestor)
                <a href="{{ route('mis-reservas') }}">Mis reservas</a>
                <a href="{{ route('mis-prestamos') }}">Mis préstamos</a>
            @endunless
        @endauth
    </div>

    {{-- Nombre del usuario, su rol y la salida (solo si hay sesión) --}}
    @auth
        <div>
            <span class="nombre-usuario">{{ $usuarioSesion->nombre }}</span>
            <span class="insignia insignia-rol">{{ $usuarioSesion->rol }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="salir">Salir</button>
            </form>
        </div>
    @endauth
</nav>

@if ($esGestor)
    {{-- Gestores: menú lateral de administración + contenido a la derecha --}}
    <div class="marco">
        <aside class="menu-lateral">
            <h2 class="menu-titulo">ADMINISTRACIÓN</h2>

            {{-- El enlace de la página actual se resalta con la clase "activo" --}}
            <a href="{{ route('panel') }}"
               class="menu-enlace{{ request()->routeIs('panel') ? ' activo' : '' }}">Panel</a>
            <a href="{{ route('libros.index') }}"
               class="menu-enlace{{ request()->routeIs('libros.*') ? ' activo' : '' }}">Libros</a>
            <a href="{{ route('categorias.index') }}"
               class="menu-enlace{{ request()->routeIs('categorias.*') ? ' activo' : '' }}">Categorías</a>
            <a href="{{ route('reservas.index') }}"
               class="menu-enlace{{ request()->routeIs('reservas.*') ? ' activo' : '' }}">Reservas</a>
            <a href="{{ route('prestamos.index') }}"
               class="menu-enlace{{ request()->routeIs('prestamos.*') ? ' activo' : '' }}">Préstamos</a>
            <a href="{{ route('multas.index') }}"
               class="menu-enlace{{ request()->routeIs('multas.*') ? ' activo' : '' }}">Multas</a>

            {{-- Configuración: solo el administrador --}}
            @if ($usuarioSesion->rol === 'administrador')
                <a href="{{ route('configuracion.edit') }}"
                   class="menu-enlace{{ request()->routeIs('configuracion.*') ? ' activo' : '' }}">Configuración</a>
            @endif
        </aside>

        <main class="contenedor">
            @include('layouts.mensajes')
            @yield('contenido')
        </main>
    </div>
@else
    {{-- Estudiantes e invitados: solo el contenido centrado --}}
    <main class="contenedor">
        @include('layouts.mensajes')
        @yield('contenido')
    </main>
@endif

{{-- Pie de página con la misma franja dorada arriba --}}
<footer class="pie">BiblioTech · Sistema de biblioteca</footer>

</body>
</html>
