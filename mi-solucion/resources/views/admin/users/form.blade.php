@extends('layouts.app')

@section('title', ($user ? 'Editar' : 'Crear') . ' Usuario – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.users') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Usuarios</a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">{{ $user ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
</div>

<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
    <form method="POST"
          action="{{ $user ? route('admin.users.update', $user->id) : route('admin.users.store') }}">
        @csrf
        @if($user) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                <select name="role" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="alumno" {{ old('role', $user->role ?? 'alumno') === 'alumno' ? 'selected' : '' }}>Alumno</option>
                    <option value="maestro" {{ old('role', $user->role ?? '') === 'maestro' ? 'selected' : '' }}>Maestro</option>
                    <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Contraseña {{ $user ? '(dejar vacío para no cambiar)' : '' }} <span class="{{ $user ? 'hidden' : '' }} text-red-500">*</span>
                </label>
                <input type="password" name="password" {{ $user ? '' : 'required' }}
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                <input type="password" name="password_confirmation"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        {{-- Course enrollment (for alumnos) --}}
        @if(isset($courses) && $courses->count())
            <div class="mt-6 border-t pt-5">
                <label class="block text-sm font-bold text-blue-900 mb-2">Inscripción a cursos (aplica si es alumno)</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($courses as $course)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="courses[]" value="{{ $course->id }}"
                                   {{ in_array($course->id, old('courses', $enrolledCourses ?? [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            {{ $course->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                {{ $user ? 'Actualizar' : 'Crear Usuario' }}
            </button>
            <a href="{{ route('admin.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg text-sm transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
