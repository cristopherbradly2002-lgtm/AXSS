@extends('layouts.app')

@section('title', 'Reporte de Asistencia – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.dashboard') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Panel</a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">Reporte de Asistencia</h2>
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
            <label class="block text-xs text-gray-500 mb-1">Alumno</label>
            <select name="student_id" class="border rounded-lg px-3 py-1.5 text-sm">
                <option value="">Todos</option>
                @foreach ($students as $s)
                    <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Desde</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border rounded-lg px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Hasta</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border rounded-lg px-3 py-1.5 text-sm">
        </div>
        <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">Filtrar</button>
        @if(request()->hasAny(['course_id','student_id','date_from','date_to']))
            <a href="{{ route('admin.attendance-report') }}" class="text-sm text-red-500 hover:underline">Limpiar</a>
        @endif
    </form>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
    <div class="bg-white rounded-xl shadow p-4 text-center">
        <p class="text-xs text-gray-400 uppercase">Total</p>
        <p class="text-2xl font-extrabold text-blue-900">{{ $summary['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-4 text-center">
        <p class="text-xs text-gray-400 uppercase">Presentes</p>
        <p class="text-2xl font-extrabold text-green-600">{{ $summary['present'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-4 text-center">
        <p class="text-xs text-gray-400 uppercase">Ausentes</p>
        <p class="text-2xl font-extrabold text-red-500">{{ $summary['absent'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-4 text-center">
        <p class="text-xs text-gray-400 uppercase">Por QR</p>
        <p class="text-2xl font-extrabold text-indigo-600">{{ $summary['qr'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-4 text-center">
        <p class="text-xs text-gray-400 uppercase">Manual</p>
        <p class="text-2xl font-extrabold text-yellow-600">{{ $summary['manual'] }}</p>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-blue-50">
            <tr>
                <th class="text-left px-4 py-3 text-blue-900 font-semibold">Fecha</th>
                <th class="text-left px-4 py-3 text-blue-900 font-semibold">Alumno</th>
                <th class="text-left px-4 py-3 text-blue-900 font-semibold">Curso</th>
                <th class="text-left px-4 py-3 text-blue-900 font-semibold">Tema</th>
                <th class="text-center px-4 py-3 text-blue-900 font-semibold">Asistencia</th>
                <th class="text-center px-4 py-3 text-blue-900 font-semibold">Método</th>
                <th class="text-left px-4 py-3 text-blue-900 font-semibold">Marcado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($attendances as $att)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-600">{{ $att->class_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-800">{{ $att->user->name ?? '?' }}</div>
                        <div class="text-xs text-gray-400">{{ $att->user->email ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $att->schedule->course->name ?? '?' }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        @if($att->lesson)
                            <span class="text-blue-600 font-semibold">#{{ $att->lesson->order }}</span> {{ $att->lesson->title }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($att->attended)
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Presente</span>
                        @else
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600">Ausente</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium {{ ($att->marked_via ?? '') === 'qr' ? 'text-indigo-600' : 'text-yellow-600' }}">
                            {{ strtoupper($att->marked_via ?? '—') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if($att->marked_at)
                            {{ $att->marked_at->format('d/m/Y H:i') }}
                            @if($att->marked_at_tz)
                                <span class="text-gray-400">({{ $att->marked_at_tz }})</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No se encontraron registros de asistencia.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($attendances->hasPages())
    <div class="mt-4">{{ $attendances->links() }}</div>
@endif

@endsection
