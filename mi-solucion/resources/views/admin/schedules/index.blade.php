@extends('layouts.app')

@section('title', 'Horarios – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Panel</a>
        <span class="text-gray-300">/</span>
        <h2 class="text-2xl font-bold text-blue-900">Horarios</h2>
    </div>
    <a href="{{ route('admin.schedules.create') }}"
       class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        + Nuevo Horario
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow px-5 py-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Curso</label>
            <select name="course_id" class="border rounded-lg px-3 py-1.5 text-sm">
                <option value="">Todos</option>
                @foreach ($courses as $c)
                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Maestro</label>
            <select name="teacher_id" class="border rounded-lg px-3 py-1.5 text-sm">
                <option value="">Todos</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">Filtrar</button>
        @if(request()->hasAny(['course_id','teacher_id']))
            <a href="{{ route('admin.schedules') }}" class="text-sm text-red-500 hover:underline">Limpiar</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-blue-50">
            <tr>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Curso</th>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Maestro</th>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Salón</th>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Día</th>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Horario</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Estado</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($schedules as $schedule)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 font-semibold text-gray-800">{{ $schedule->course->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $schedule->teacher->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $schedule->classroom->name }}</td>
                    <td class="px-5 py-3 text-blue-700 font-semibold">{{ $schedule->day_of_week }}</td>
                    <td class="px-5 py-3 text-gray-600">
                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $schedule->active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                            {{ $schedule->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 text-xs font-medium px-3 py-1 rounded transition">
                                Editar
                            </a>
                            <form method="POST" action="{{ route('admin.schedules.delete', $schedule->id) }}"
                                  onsubmit="return confirm('¿Eliminar este horario?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1 rounded transition">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No hay horarios registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
