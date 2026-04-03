@extends('layouts.app')

@section('title', 'Lista de Alumnos – AXSS')

@section('content')

{{-- Back + Title --}}
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('maestro.dashboard') }}"
        class="text-blue-700 hover:text-blue-900 text-sm flex items-center gap-1 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Panel
    </a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">Lista de Alumnos</h2>
</div>

{{-- Schedule Info --}}
<div class="bg-blue-900 text-white rounded-xl px-6 py-4 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div>
        <p class="text-blue-300 text-xs uppercase tracking-wide">Curso</p>
        <p class="font-bold mt-0.5">{{ $schedule->course->name }}</p>
    </div>
    <div>
        <p class="text-blue-300 text-xs uppercase tracking-wide">Salón</p>
        <p class="font-bold mt-0.5">{{ $schedule->classroom->name }}</p>
    </div>
    <div>
        <p class="text-blue-300 text-xs uppercase tracking-wide">Día y Hora</p>
        <p class="font-bold mt-0.5">
            {{ $schedule->day_of_week }}
            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
        </p>
    </div>
    <div>
        <p class="text-blue-300 text-xs uppercase tracking-wide">Fecha de clase</p>
        <p class="font-bold mt-0.5">
            {{ \Carbon\Carbon::parse($classDate)->isoFormat('D [de] MMMM, YYYY') }}
        </p>
    </div>
</div>

{{-- Date selector --}}
<div class="bg-white rounded-xl shadow px-5 py-4 mb-6 flex flex-wrap items-center gap-3">
    <label class="text-sm font-medium text-gray-700">Cambiar fecha de clase:</label>
    <form method="GET" action="{{ route('maestro.lista-alumnos', $schedule->id) }}" class="flex gap-2">
        <input
            type="date"
            name="class_date"
            value="{{ $classDate }}"
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <button type="submit"
            class="bg-blue-700 hover:bg-blue-800 text-white text-sm px-4 py-1.5 rounded-lg transition">
            Ver
        </button>
    </form>
</div>

@if ($registrations->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
        <p class="text-lg font-medium">No hay alumnos registrados para esta clase en la fecha seleccionada.</p>
    </div>
@else
    {{-- Attendance Form --}}
    <form method="POST" action="{{ route('maestro.guardar-clase') }}">
        @csrf
        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
        <input type="hidden" name="class_date" value="{{ $classDate }}">

        {{-- Lesson selector --}}
        <div class="bg-white rounded-xl shadow px-5 py-4 mb-5">
            <label class="block text-sm font-bold text-blue-900 mb-2">
                Tema impartido en esta clase
                <span class="text-red-500">*</span>
            </label>
            <select name="lesson_id" required
                class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="" disabled selected>-- Selecciona el tema --</option>
                @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->id }}">
                        {{ $lesson->order }}. {{ $lesson->title }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Students table --}}
        <div class="bg-white rounded-xl shadow overflow-hidden mb-5">
            <table class="w-full text-sm">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Alumno</th>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Lección actual</th>
                        <th class="text-center px-5 py-3 text-blue-900 font-semibold">Asistencia</th>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Notas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($registrations as $registration)
                        @php
                            $lessonNum = $registration->current_lesson;
                            $lessonObj = $lessons->firstWhere('id', $lessonNum);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <div class="font-semibold text-gray-800">{{ $registration->user->name }}</div>
                                <div class="text-xs text-gray-400">{{ $registration->user->email }}</div>
                            </td>
                            <td class="px-5 py-3">
                                @if ($lessonObj)
                                    <span class="text-gray-700">
                                        <span class="text-blue-600 font-semibold">#{{ $lessonObj->order }}</span>
                                        {{ $lessonObj->title }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic text-xs">Sin avance registrado</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-center gap-4">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio"
                                            name="attendance[{{ $registration->id }}]"
                                            value="1"
                                            class="text-green-600 focus:ring-green-500"
                                            required>
                                        <span class="text-green-700 font-medium text-xs">Presente</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio"
                                            name="attendance[{{ $registration->id }}]"
                                            value="0"
                                            class="text-red-500 focus:ring-red-400">
                                        <span class="text-red-600 font-medium text-xs">Ausente</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <input type="text"
                                    name="notes[{{ $registration->id }}]"
                                    placeholder="Observaciones (opcional)"
                                    class="border border-gray-200 rounded px-2 py-1 text-xs w-full focus:outline-none focus:ring-1 focus:ring-blue-400">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Save button --}}
        <div class="flex justify-end">
            <button type="submit"
                class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-xl shadow transition text-sm">
                Guardar Clase y Asistencia
            </button>
        </div>
    </form>
@endif

@endsection
