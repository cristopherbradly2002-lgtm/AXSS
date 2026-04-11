@extends('layouts.app')

@section('title', 'Salones – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Panel</a>
        <span class="text-gray-300">/</span>
        <h2 class="text-2xl font-bold text-blue-900">Salones</h2>
    </div>
    <a href="{{ route('admin.classrooms.create') }}"
       class="bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        + Nuevo Salón
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse ($classrooms as $classroom)
        <div class="bg-white rounded-xl shadow p-5">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-blue-900 text-lg">{{ $classroom->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $classroom->location ?? 'Sin ubicación' }}</p>
                </div>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">
                    {{ $classroom->capacity }} cupos
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $classroom->schedules_count }} horario(s) asignados</p>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('admin.classrooms.edit', $classroom->id) }}"
                   class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 text-xs font-medium px-3 py-1 rounded transition">
                    Editar
                </a>
                <form method="POST" action="{{ route('admin.classrooms.delete', $classroom->id) }}"
                      onsubmit="return confirm('¿Eliminar «{{ $classroom->name }}»?')">
                    @csrf @method('DELETE')
                    <button class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1 rounded transition">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-3 bg-white rounded-xl shadow p-10 text-center text-gray-400">
            No hay salones registrados.
        </div>
    @endforelse
</div>

@endsection
