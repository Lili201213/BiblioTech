@extends('layouts.app')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    {{-- Formulario de ingreso centrado sobre el fondo lavanda --}}
    <div class="tarjeta caja">
        <p class="login-emoji">📚</p>
        <h1>Bienvenido a BiblioTech</h1>

        <form action="{{ route('login.attempt') }}" method="POST">
            @csrf

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required
                   value="{{ old('email') }}">

            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            {{-- Sin "recordarme": la tabla usuarios no tiene remember_token --}}
            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
@endsection
