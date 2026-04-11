@extends('layouts.app')

@section('title', ($course ? 'Editar' : 'Crear') . ' Curso – Admin AXSS')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.courses') }}" class="text-blue-700 hover:text-blue-900 text-sm">← Cursos</a>
    <span class="text-gray-300">/</span>
    <h2 class="text-2xl font-bold text-blue-900">{{ $course ? 'Editar Curso' : 'Nuevo Curso' }}</h2>
</div>

<div class="bg-white rounded-xl shadow p-6 max-w-3xl">
    <form method="POST"
          action="{{ $course ? route('admin.courses.update', $course->id) : route('admin.courses.store') }}">
        @csrf
        @if($course) @method('PUT') @endif

        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del curso <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $course->name ?? '') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="description" rows="3"
                          class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description', $course->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1"
                           {{ old('active', $course->active ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Curso activo
                </label>
            </div>

            {{-- Lessons --}}
            <div>
                <label class="block text-sm font-bold text-blue-900 mb-2">Temario (un tema por línea)</label>
                <div id="lessons-container" class="space-y-2">
                    @php
                        $existingLessons = old('lessons', $course ? $course->lessons->pluck('title')->toArray() : ['']);
                    @endphp
                    @foreach ($existingLessons as $i => $title)
                        <div class="flex gap-2 items-center lesson-row">
                            <span class="text-xs text-gray-400 w-6 text-right">{{ $i + 1 }}.</span>
                            <input type="text" name="lessons[]" value="{{ $title }}" placeholder="Título del tema"
                                   class="flex-1 border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <button type="button" onclick="this.closest('.lesson-row').remove();reindexLessons()"
                                    class="text-red-400 hover:text-red-600 text-xs">✕</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-lesson"
                        class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                    + Agregar tema
                </button>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                {{ $course ? 'Actualizar' : 'Crear Curso' }}
            </button>
            <a href="{{ route('admin.courses') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg text-sm transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('add-lesson').addEventListener('click', function(){
    var container = document.getElementById('lessons-container');
    var count = container.querySelectorAll('.lesson-row').length;
    var div = document.createElement('div');
    div.className = 'flex gap-2 items-center lesson-row';
    div.innerHTML = '<span class="text-xs text-gray-400 w-6 text-right">' + (count + 1) + '.</span>' +
        '<input type="text" name="lessons[]" placeholder="Título del tema" class="flex-1 border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">' +
        '<button type="button" onclick="this.closest(\'.lesson-row\').remove();reindexLessons()" class="text-red-400 hover:text-red-600 text-xs">✕</button>';
    container.appendChild(div);
    div.querySelector('input').focus();
});

function reindexLessons(){
    document.querySelectorAll('#lessons-container .lesson-row').forEach(function(row, i){
        row.querySelector('span').textContent = (i + 1) + '.';
    });
}
</script>
@endpush
