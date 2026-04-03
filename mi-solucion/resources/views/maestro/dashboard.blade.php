@extends('layouts.app')

@section('title', 'Panel del Maestro – AXSS')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-blue-900">Panel del Maestro</h2>
    <p class="text-gray-500 text-sm mt-1">Bienvenido, <strong>{{ Auth::user()->name }}</strong>. Aquí están tus clases asignadas.</p>
</div>

@if ($schedules->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
        <svg class="w-14 h-14 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-lg font-medium">No tienes horarios asignados aún.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($schedules as $schedule)
            <div class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="bg-blue-900 px-5 py-4">
                    <h3 class="text-white font-bold text-lg leading-snug">{{ $schedule->course->name }}</h3>
                    <p class="text-blue-300 text-xs mt-1">{{ $schedule->classroom->name }} · {{ $schedule->classroom->location }}</p>
                </div>

                {{-- Body --}}
                <div class="px-5 py-4 flex-1 space-y-3">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-semibold text-blue-900">{{ $schedule->day_of_week }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                        – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>
                            <strong class="text-blue-900 text-lg">{{ $schedule->students_count }}</strong>
                            alumno(s) hoy
                            <span class="text-gray-400 text-xs">/ {{ $schedule->classroom->capacity }} cupo</span>
                        </span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-5 pb-4">
                    <a href="{{ route('maestro.lista-alumnos', $schedule->id) }}"
                        class="block w-full text-center bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-semibold py-2.5 rounded-lg text-sm transition">
                        Ver Lista de Alumnos
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
