@extends('layouts.app')

@section('title', 'Asistencia registrada')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-xl shadow p-8 text-center">
    <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <h2 class="text-xl font-bold text-green-700">{{ $message ?? 'Asistencia registrada correctamente.' }}</h2>
    <p class="text-gray-600 mt-2">Si hubo algún problema, contacta con tu profesor.</p>
    <div class="mt-6">
        <a href="{{ url()->previous() ?: '/' }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">Volver</a>
    </div>
</div>
@endsection
