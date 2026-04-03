@extends('layouts.app')

@section('title', 'Horarios Disponibles – AXSS')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('alumno.mis-cursos') }}"
        class="text-blue-700 hover:text-blue-900 text-sm flex items-center gap-1 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Mis Cursos
    </a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">Horarios Disponibles</h2>
</div>

{{-- Regla de 24 horas (aviso informativo) --}}
<div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 text-sm px-4 py-3 rounded mb-6 flex items-start gap-2">
    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
    </svg>
    <span><strong>Recuerda:</strong> para asignarte un horario debes hacerlo con <strong>24 horas de anticipación</strong>.</span>
</div>

@php
    $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    $byDay = $schedules->groupBy('day_of_week');
@endphp

@if ($schedules->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
        <p class="text-lg font-medium">No hay horarios disponibles para este curso.</p>
    </div>
@else

{{-- Calendario semanal --}}
<div class="overflow-x-auto">
    <div class="grid grid-cols-7 gap-2 min-w-[900px]">
        @foreach ($days as $day)
            <div class="flex flex-col">
                {{-- Cabecera del día --}}
                <div class="bg-blue-900 text-white text-center text-xs font-bold py-2 rounded-t-lg">
                    {{ $day }}
                </div>
                <div class="bg-white rounded-b-lg shadow min-h-[140px] p-2 flex flex-col gap-2">

                    @if (isset($byDay[$day]) && $byDay[$day]->isNotEmpty())
                        @foreach ($byDay[$day] as $schedule)
                            <div class="rounded-lg border text-xs {{ $schedule->available ? 'border-green-300 bg-green-50' : 'border-red-200 bg-red-50' }} p-2">
                                {{-- Hora --}}
                                <p class="font-bold text-blue-900">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                    – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </p>
                                {{-- Maestro --}}
                                <p class="text-gray-500 mt-0.5">{{ $schedule->teacher->name }}</p>
                                {{-- Salón y cupo --}}
                                <p class="text-gray-400 mt-0.5">
                                    {{ $schedule->classroom->name }}
                                    · {{ $schedule->registered_count }}/{{ $schedule->classroom->capacity }} alumnos
                                </p>
                                {{-- Badge disponibilidad --}}
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-xs font-semibold
                                    {{ $schedule->available ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                    {{ $schedule->available ? 'Disponible' : 'Lleno' }}
                                </span>

                                @if ($schedule->available)
                                {{-- Formulario de registro --}}
                                <form method="POST" action="{{ route('alumno.registrar-clase') }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                    <input
                                        type="date"
                                        name="class_date"
                                        value="{{ $schedule->next_date }}"
                                        min="{{ \Carbon\Carbon::now()->addHours(24)->format('Y-m-d') }}"
                                        class="w-full border border-gray-300 rounded px-1.5 py-1 text-xs mb-1 focus:outline-none focus:ring-1 focus:ring-blue-400"
                                    >
                                    <button type="submit"
                                        class="w-full bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold py-1.5 rounded transition">
                                        Registrarme
                                    </button>
                                </form>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-gray-300 text-center mt-4">Sin clases</p>
                    @endif

                </div>
            </div>
        @endforeach
    </div>
</div>

@endif

@endsection
