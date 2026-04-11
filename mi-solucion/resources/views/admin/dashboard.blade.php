@extends('layouts.app')

@section('title', 'Panel de Administración – AXSS')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-blue-900">Panel de Administración</h2>
    <p class="text-gray-500 text-sm mt-1">Bienvenido, <strong>{{ Auth::user()->name }}</strong>. Vista general del sistema.</p>
</div>

{{-- Quick Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Usuarios</p>
        <p class="text-3xl font-extrabold text-blue-900 mt-1">{{ $stats['total_users'] }}</p>
        <p class="text-xs text-gray-500 mt-1">
            {{ $stats['total_admins'] }} admin · {{ $stats['total_maestros'] }} maestros · {{ $stats['total_alumnos'] }} alumnos
        </p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Cursos activos</p>
        <p class="text-3xl font-extrabold text-green-700 mt-1">{{ $stats['active_courses'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $stats['total_courses'] }} total</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Clases hoy</p>
        <p class="text-3xl font-extrabold text-yellow-600 mt-1">{{ $stats['today_classes'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $stats['today_attendance'] }} asistencia(s)</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Horarios activos</p>
        <p class="text-3xl font-extrabold text-indigo-700 mt-1">{{ $stats['total_schedules'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $stats['total_classrooms'] }} salones</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-8">
    <a href="{{ route('admin.users') }}" class="bg-blue-700 hover:bg-blue-800 text-white text-center text-sm font-semibold py-3 rounded-xl transition">
        Usuarios
    </a>
    <a href="{{ route('admin.courses') }}" class="bg-green-700 hover:bg-green-800 text-white text-center text-sm font-semibold py-3 rounded-xl transition">
        Cursos
    </a>
    <a href="{{ route('admin.classrooms') }}" class="bg-purple-700 hover:bg-purple-800 text-white text-center text-sm font-semibold py-3 rounded-xl transition">
        Salones
    </a>
    <a href="{{ route('admin.schedules') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white text-center text-sm font-semibold py-3 rounded-xl transition">
        Horarios
    </a>
    <a href="{{ route('admin.attendance-report') }}" class="bg-red-600 hover:bg-red-700 text-white text-center text-sm font-semibold py-3 rounded-xl transition">
        Asistencia
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Course Attendance Rates --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="bg-blue-900 px-5 py-3">
            <h3 class="text-white font-bold">Tasa de Asistencia por Curso</h3>
        </div>
        <div class="p-5 space-y-3">
            @forelse ($courseStats as $course)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">{{ $course->name }}</span>
                        <span class="text-gray-500">{{ $course->attendance_rate }}% ({{ $course->total_records }} registros)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full {{ $course->attendance_rate >= 70 ? 'bg-green-500' : ($course->attendance_rate >= 40 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $course->attendance_rate }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-sm">No hay cursos activos.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="bg-blue-900 px-5 py-3">
            <h3 class="text-white font-bold">Actividad Reciente</h3>
        </div>
        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
            @forelse ($recentActivity as $act)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full {{ $act->attended ? 'bg-green-500' : 'bg-red-400' }} shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 truncate">
                            <strong>{{ $act->user->name ?? '?' }}</strong>
                            — {{ $act->schedule->course->name ?? '?' }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $act->lesson->title ?? 'Sin tema' }}
                            · {{ strtoupper($act->marked_via ?? '?') }}
                            · {{ $act->marked_at ? $act->marked_at->diffForHumans() : '' }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-6 text-center text-gray-400 text-sm">Sin actividad reciente.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
