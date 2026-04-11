@extends('layouts.app')

@section('title', 'Cursos – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Panel</a>
        <span class="text-gray-300">/</span>
        <h2 class="text-2xl font-bold text-blue-900">Cursos</h2>
    </div>
    <a href="{{ route('admin.courses.create') }}"
       class="bg-green-700 hover:bg-green-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        + Nuevo Curso
    </a>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-blue-50">
            <tr>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Curso</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Temas</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Alumnos</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Horarios</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Estado</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($courses as $course)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="font-semibold text-gray-800">{{ $course->name }}</div>
                        <div class="text-xs text-gray-400">{{ Str::limit($course->description, 60) }}</div>
                    </td>
                    <td class="px-5 py-3 text-center font-bold text-blue-700">{{ $course->lessons_count }}</td>
                    <td class="px-5 py-3 text-center font-bold text-green-700">{{ $course->users_count }}</td>
                    <td class="px-5 py-3 text-center font-bold text-yellow-700">{{ $course->schedules_count }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $course->active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                            {{ $course->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('admin.courses.edit', $course->id) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 text-xs font-medium px-3 py-1 rounded transition">
                                Editar
                            </a>
                            <form method="POST" action="{{ route('admin.courses.delete', $course->id) }}"
                                  onsubmit="return confirm('¿Eliminar el curso «{{ $course->name }}» y todos sus temas?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1 rounded transition">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No hay cursos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
