@extends('layouts.app')

@section('title', ($classroom ? 'Editar' : 'Crear') . ' Salón – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.classrooms') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Salones</a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">{{ $classroom ? 'Editar Salón' : 'Nuevo Salón' }}</h2>
</div>

<div class="bg-white rounded-xl shadow p-6 max-w-lg">
    <form method="POST"
          action="{{ $classroom ? route('admin.classrooms.update', $classroom->id) : route('admin.classrooms.store') }}">
        @csrf
        @if($classroom) @method('PUT') @endif

        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $classroom->name ?? '') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad (alumnos) <span class="text-red-500">*</span></label>
                <input type="number" name="capacity" value="{{ old('capacity', $classroom->capacity ?? 3) }}" required min="1" max="50"
                       class="w-32 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                <input type="text" name="location" value="{{ old('location', $classroom->location ?? '') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                {{ $classroom ? 'Actualizar' : 'Crear Salón' }}
            </button>
            <a href="{{ route('admin.classrooms') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg text-sm transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
