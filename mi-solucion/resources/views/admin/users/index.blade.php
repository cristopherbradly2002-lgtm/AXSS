@extends('layouts.app')

@section('title', 'Usuarios – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Panel</a>
        <span class="text-gray-300">/</span>
        <h2 class="text-2xl font-bold text-blue-900">Usuarios</h2>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        + Nuevo Usuario
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow px-5 py-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o email..."
                   class="border rounded-lg px-3 py-1.5 text-sm w-48 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Rol</label>
            <select name="role" class="border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="maestro" {{ request('role') === 'maestro' ? 'selected' : '' }}>Maestro</option>
                <option value="alumno" {{ request('role') === 'alumno' ? 'selected' : '' }}>Alumno</option>
            </select>
        </div>
        <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">Filtrar</button>
        @if(request()->hasAny(['search','role']))
            <a href="{{ route('admin.users') }}" class="text-sm text-red-500 hover:underline">Limpiar</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-blue-50">
            <tr>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">ID</th>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Nombre</th>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Email</th>
                <th class="text-left px-5 py-3 text-blue-900 font-semibold">Teléfono</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Rol</th>
                <th class="text-center px-5 py-3 text-blue-900 font-semibold">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-400">{{ $user->id }}</td>
                    <td class="px-5 py-3 font-semibold text-gray-800">{{ $user->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $user->phone ?? '—' }}</td>
                    <td class="px-5 py-3 text-center">
                        @php
                            $roleColors = ['admin' => 'bg-red-100 text-red-700', 'maestro' => 'bg-blue-100 text-blue-700', 'alumno' => 'bg-green-100 text-green-700'];
                        @endphp
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $roleColors[$user->role] ?? '' }}">
                            {{ strtoupper($user->role) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 text-xs font-medium px-3 py-1 rounded transition">
                                Editar
                            </a>
                            @if($user->role !== 'admin' || $user->id !== Auth::id())
                                <a href="{{ route('admin.impersonate', $user->id) }}"
                                   class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-medium px-3 py-1 rounded transition"
                                   title="Entrar como este usuario">
                                    Suplantar
                                </a>
                            @endif
                            @if($user->id !== Auth::id())
                                <form method="POST" action="{{ route('admin.users.delete', $user->id) }}"
                                      onsubmit="return confirm('¿Eliminar a {{ $user->name }}? Esta acción no se puede deshacer.')">
                                    @csrf @method('DELETE')
                                    <button class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1 rounded transition">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No se encontraron usuarios.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div class="mt-4">{{ $users->links() }}</div>
@endif

@endsection
