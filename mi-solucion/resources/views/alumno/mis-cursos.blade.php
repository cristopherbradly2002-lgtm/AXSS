@extends('layouts.app')

@section('title', 'Mis Cursos – AXSS')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-blue-900">Mis Cursos</h2>
    <p class="text-gray-500 text-sm mt-1">Revisa tu progreso y accede a los horarios disponibles.</p>
</div>

@if ($courses->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
        <svg class="w-14 h-14 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p class="text-lg font-medium">No tienes cursos inscritos aún.</p>
        <p class="text-sm mt-1">Ponte en contacto con tu coordinador para inscribirte.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($courses as $item)
            @php $course = $item['course']; @endphp
            <div class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden flex flex-col">
                {{-- Card header --}}
                <div class="bg-blue-900 px-5 py-4">
                    <h3 class="text-white font-bold text-lg leading-snug">{{ $course->name }}</h3>
                    @if ($course->description)
                        <p class="text-blue-300 text-xs mt-1 line-clamp-2">{{ $course->description }}</p>
                    @endif
                </div>

                {{-- Progress --}}
                <div class="px-5 py-4 flex-1">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Progreso</span>
                        <span class="font-semibold text-blue-900">{{ $item['progress_percent'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                        <div
                            class="bg-blue-600 h-2.5 rounded-full transition-all"
                            style="width: {{ $item['progress_percent'] }}%">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-blue-50 rounded-lg p-2">
                            <p class="text-blue-900 font-bold text-xl">{{ $item['total_lessons'] }}</p>
                            <p class="text-gray-500 text-xs">Total</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-2">
                            <p class="text-green-700 font-bold text-xl">{{ $item['current_lesson'] }}</p>
                            <p class="text-gray-500 text-xs">Vistas</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-2">
                            <p class="text-yellow-600 font-bold text-xl">{{ $item['remaining'] }}</p>
                            <p class="text-gray-500 text-xs">Restantes</p>
                        </div>
                    </div>
                </div>

                {{-- Footer button --}}
                <div class="px-5 pb-4">
                    <a href="{{ route('alumno.detalle-curso', $course->id) }}"
                        class="block w-full text-center bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-semibold py-2.5 rounded-lg text-sm transition">
                        Detalles
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
