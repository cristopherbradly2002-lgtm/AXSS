@extends('layouts.app')

@section('title', $course->name . ' – AXSS')

@section('content')

{{-- Back --}}
<div class="mb-5 flex items-center gap-3">
    <a href="{{ route('alumno.mis-cursos') }}"
        class="text-blue-700 hover:text-blue-900 text-sm flex items-center gap-1 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Mis Cursos
    </a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">{{ $course->name }}</h2>
</div>

{{-- Tabs --}}
<div x-data="{ tab: 'progreso' }">

    <div class="flex border-b border-gray-200 mb-6 gap-1">
        <button @click="tab = 'progreso'"
            :class="tab === 'progreso' ? 'border-b-2 border-blue-700 text-blue-700 font-semibold' : 'text-gray-500 hover:text-blue-700'"
            class="px-5 py-2.5 text-sm transition focus:outline-none">
            Progreso
        </button>
        <button @click="tab = 'temario'"
            :class="tab === 'temario' ? 'border-b-2 border-blue-700 text-blue-700 font-semibold' : 'text-gray-500 hover:text-blue-700'"
            class="px-5 py-2.5 text-sm transition focus:outline-none">
            Temario
        </button>
        <button @click="tab = 'salones'"
            :class="tab === 'salones' ? 'border-b-2 border-blue-700 text-blue-700 font-semibold' : 'text-gray-500 hover:text-blue-700'"
            class="px-5 py-2.5 text-sm transition focus:outline-none">
            Salones
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: PROGRESO                                                --}}
    {{-- ============================================================ --}}
    <div x-show="tab === 'progreso'" x-cloak>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            {{-- Card progreso general --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-blue-900 font-bold text-lg mb-4">Progreso del curso</h3>

                <div class="flex justify-between text-sm text-gray-500 mb-1">
                    <span>Avance general</span>
                    <span class="font-semibold text-blue-900">{{ $progressPercent }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
                    <div class="bg-blue-600 h-3 rounded-full transition-all"
                        style="width: {{ $progressPercent }}%"></div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-blue-50 rounded-xl p-3">
                        <p class="text-2xl font-extrabold text-blue-900">{{ $totalLessons }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Total lecciones</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <p class="text-2xl font-extrabold text-green-700">{{ $currentLesson }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Completadas</p>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-3">
                        <p class="text-2xl font-extrabold text-yellow-600">{{ $remaining }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Restantes</p>
                    </div>
                </div>
            </div>

            {{-- Card lección actual --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-blue-900 font-bold text-lg mb-4">Siguiente lección</h3>
                @php $nextLesson = $course->lessons->firstWhere('order', $currentLesson + 1); @endphp
                @if ($nextLesson)
                    <div class="flex items-start gap-3">
                        <div class="bg-blue-900 text-yellow-400 font-extrabold text-lg rounded-lg w-10 h-10 flex items-center justify-center shrink-0">
                            {{ $nextLesson->order }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $nextLesson->title }}</p>
                            @if ($nextLesson->description)
                                <p class="text-sm text-gray-400 mt-1">{{ $nextLesson->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-5">
                        <button @click="tab = 'salones'"
                            class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-semibold text-sm px-5 py-2.5 rounded-lg transition">
                            Reservar clase →
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-3 text-green-700">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-lg">¡Curso completado!</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: TEMARIO                                                 --}}
    {{-- ============================================================ --}}
    <div x-show="tab === 'temario'" x-cloak>
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-blue-900 px-6 py-4">
                <h3 class="text-white font-bold text-lg">Temario – {{ $course->name }}</h3>
                <p class="text-blue-300 text-xs mt-0.5">{{ $totalLessons }} lecciones en total</p>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach ($course->lessons as $lesson)
                    @php
                        $isDone    = $lesson->order <= $currentLesson;
                        $isCurrent = $lesson->order === $currentLesson + 1;
                    @endphp
                    <li class="flex items-center gap-4 px-6 py-3.5
                        {{ $isCurrent ? 'bg-blue-50' : '' }}
                        {{ $isDone    ? 'opacity-60' : '' }}">

                        {{-- Icono estado --}}
                        <div class="shrink-0">
                            @if ($isDone)
                                <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @elseif ($isCurrent)
                                <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center">
                                    <span class="text-white text-xs font-bold">{{ $lesson->order }}</span>
                                </div>
                            @else
                                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500 text-xs font-semibold">{{ $lesson->order }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Título --}}
                        <div class="flex-1">
                            <p class="text-sm font-medium {{ $isCurrent ? 'text-blue-900' : 'text-gray-700' }}">
                                {{ $lesson->title }}
                            </p>
                        </div>

                        {{-- Badge --}}
                        @if ($isCurrent)
                            <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full">
                                Siguiente
                            </span>
                        @elseif ($isDone)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                Vista
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: SALONES                                                 --}}
    {{-- ============================================================ --}}
    <div x-show="tab === 'salones'" x-cloak>

        {{-- Aviso 24 horas --}}
        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 text-sm px-4 py-3 rounded mb-5 flex items-start gap-2">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>Recuerda: para asignarte un horario debes hacerlo con <strong>24 horas de anticipación</strong>.</span>
        </div>

        @if ($schedules->isEmpty())
            <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
                <p class="text-lg font-medium">No hay salones disponibles para este curso.</p>
            </div>
        @else

        @php
            $days  = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            $byDay = $schedules->groupBy('day_of_week');
        @endphp

        <div class="overflow-x-auto">
            <div class="grid grid-cols-7 gap-2 min-w-[900px]">
                @foreach ($days as $day)
                    <div class="flex flex-col">
                        {{-- Cabecera día --}}
                        <div class="bg-blue-900 text-white text-center text-xs font-bold py-2 rounded-t-lg">
                            {{ $day }}
                        </div>
                        <div class="bg-white rounded-b-lg shadow min-h-[160px] p-2 flex flex-col gap-2">

                            @if (isset($byDay[$day]) && $byDay[$day]->isNotEmpty())
                                @foreach ($byDay[$day] as $schedule)
                                    <div class="rounded-lg border text-xs p-2
                                        {{ $schedule->available ? 'border-green-300 bg-green-50' : 'border-red-200 bg-red-50' }}">

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
                                        </p>

                                        {{-- Indicador cupo --}}
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full
                                                {{ $schedule->available ? 'bg-green-500' : 'bg-red-500' }}">
                                            </span>
                                            <span class="{{ $schedule->available ? 'text-green-700' : 'text-red-700' }} font-semibold">
                                                {{ $schedule->available ? 'Disponible' : 'Lleno' }}
                                            </span>
                                            <span class="text-gray-400 ml-auto">
                                                {{ $schedule->registered_count }}/{{ $schedule->classroom->capacity }}
                                            </span>
                                        </div>

                                        @if ($schedule->available)
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
    </div>

</div>{{-- end x-data --}}

{{-- Alpine.js para las tabs --}}
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>[x-cloak] { display: none !important; }</style>

@endsection
