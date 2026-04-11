@extends('layouts.app')

@section('title', ($schedule ? 'Editar' : 'Crear') . ' Horario – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.schedules') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Horarios</a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">{{ $schedule ? 'Editar Horario' : 'Nuevo Horario' }}</h2>
</div>

<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
    <form method="POST"
          action="{{ $schedule ? route('admin.schedules.update', $schedule->id) : route('admin.schedules.store') }}">
        @csrf
        @if($schedule) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Curso <span class="text-red-500">*</span></label>
                <select name="course_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Seleccionar --</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" {{ old('course_id', $schedule->course_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maestro <span class="text-red-500">*</span></label>
                <select name="teacher_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Seleccionar --</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}" {{ old('teacher_id', $schedule->teacher_id ?? '') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Salón <span class="text-red-500">*</span></label>
                <select name="classroom_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Seleccionar --</option>
                    @foreach ($classrooms as $cl)
                        <option value="{{ $cl->id }}" {{ old('classroom_id', $schedule->classroom_id ?? '') == $cl->id ? 'selected' : '' }}>{{ $cl->name }} ({{ $cl->capacity }} cupos)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Día <span class="text-red-500">*</span></label>
                <select name="day_of_week" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $day)
                        <option value="{{ $day }}" {{ old('day_of_week', $schedule->day_of_week ?? '') === $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hora inicio <span class="text-red-500">*</span></label>
                <input type="time" name="start_time" value="{{ old('start_time', $schedule ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hora fin <span class="text-red-500">*</span></label>
                <input type="time" name="end_time" value="{{ old('end_time', $schedule ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="mt-5">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1"
                       {{ old('active', $schedule->active ?? true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                Horario activo
            </label>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                {{ $schedule ? 'Actualizar' : 'Crear Horario' }}
            </button>
            <a href="{{ route('admin.schedules') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg text-sm transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
