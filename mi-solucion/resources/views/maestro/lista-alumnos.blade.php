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
            <div class="flex gap-3 items-center">
                <select id="lesson-select" name="lesson_id" required
                    class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="" disabled selected>-- Selecciona el tema --</option>
                @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->id }}">
                        {{ $lesson->order }}. {{ $lesson->title }}
                    </option>
                @endforeach
                </select>

                {{-- Botón para generar QR (abre en nueva pestaña) --}}
                <button type="button" id="open-qr-btn"
                    data-schedule-id="{{ $schedule->id }}"
                    data-class-date="{{ $classDate }}"
                    class="ml-2 bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg transition">
                    Mostrar QR
                </button>
            </div>
        </div>

        {{-- Students table --}}
        <div class="bg-white rounded-xl shadow overflow-hidden mb-5">
            <table class="w-full text-sm">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Alumno</th>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Acción</th>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Lección actual</th>
                        <th class="text-center px-5 py-3 text-blue-900 font-semibold">Asistencia</th>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Estado</th>
                        <th class="text-left px-5 py-3 text-blue-900 font-semibold">Notas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($registrations as $registration)
                        @php
                            $lessonNum = $registration->current_lesson;
                            $lessonObj = $lessons->firstWhere('id', $lessonNum);
                        @endphp
                        <tr class="hover:bg-gray-50 transition" data-registration-id="{{ $registration->id }}">
                            <td class="px-5 py-3">
                                <div class="font-semibold text-gray-800">{{ $registration->user->name }}</div>
                                <div class="text-xs text-gray-400">{{ $registration->user->email }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <button type="button" class="generate-student-qr bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1 rounded" data-student-id="{{ $registration->user->id }}">
                                    QR alumno
                                </button>
                            </td>
                            <td class="px-5 py-3" id="lesson-{{ $registration->id }}">
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
                                @php
                                    $a = isset($attendanceMap) ? ($attendanceMap->get($registration->id) ?? null) : null;
                                    if ($a) {
                                        $via = $a->marked_via ? strtoupper($a->marked_via) : 'QR';
                                        $markerName = $a->marked_by ? optional(App\Models\User::find($a->marked_by))->name : null;
                                        // Show local time for the student when available
                                        $timeLocal = null;
                                        if ($a && $a->marked_at) {
                                            try {
                                                if ($a->marked_at_tz) {
                                                    $timeLocal = $a->marked_at->copy()->setTimezone($a->marked_at_tz)->toDateTimeString() . ' (' . $a->marked_at_tz . ')';
                                                } else {
                                                    $timeLocal = $a->marked_at->toDateTimeString();
                                                }
                                            } catch (\Exception $e) {
                                                $timeLocal = $a->marked_at->toDateTimeString();
                                            }
                                        }
                                        $markerText = 'Presente — ' . $via . ($markerName ? ' por ' . $markerName : '') . ($timeLocal ? ' • ' . $timeLocal : '');
                                        $markerClass = 'text-xs text-green-700 font-medium';
                                    } else {
                                        $markerText = 'No registrado';
                                        $markerClass = 'text-xs text-gray-500';
                                    }
                                @endphp
                                <div id="marker-{{ $registration->id }}" class="{{ $markerClass }}">{{ $markerText }}</div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('open-qr-btn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var lessonSelect = document.getElementById('lesson-select');
        var lessonId = lessonSelect ? lessonSelect.value : null;
        if (!lessonId) {
            alert('Selecciona primero el tema que vas a impartir para generar el QR.');
            return;
        }
        var scheduleId = btn.dataset.scheduleId;
        var classDate = btn.dataset.classDate;
        // Use Laravel `url()` helper output so the path respects subfolder installs
        var base = '{{ url('maestro/schedule') }}';
        var url = base + '/' + encodeURIComponent(scheduleId) + '/lesson/' + encodeURIComponent(lessonId) + '/qr?date=' + encodeURIComponent(classDate);
        window.open(url, '_blank');
    });
    // Generar QR por alumno vía AJAX
    document.querySelectorAll('.generate-student-qr').forEach(function(el){
        el.addEventListener('click', function(){
            var lessonSelect = document.getElementById('lesson-select');
            var lessonId = lessonSelect ? lessonSelect.value : null;
            if (!lessonId) {
                alert('Selecciona primero el tema que vas a impartir para generar el QR.');
                return;
            }
            var studentId = this.dataset.studentId;
            var scheduleId = '{{ $schedule->id }}';
            var classDate = '{{ $classDate }}';

            fetch('{{ route('maestro.generate-student-qr') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    schedule_id: scheduleId,
                    lesson_id: lessonId,
                    student_id: studentId,
                    date: classDate
                })
            }).then(function(resp){
                return resp.json();
            }).then(function(data){
                if (data.url) {
                    // Mostrar QR como imagen en un modal/popup para que el alumno lo escanee
                    var qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(data.url);
                    var win = window.open('', '_blank', 'width=420,height=520');
                    win.document.write(
                        '<html><head><title>QR Alumno</title></head><body style="text-align:center;font-family:sans-serif;padding:20px">' +
                        '<h3>QR para el alumno</h3>' +
                        '<p style="font-size:12px;color:#666">El alumno debe escanear este código.</p>' +
                        '<img src="' + qrImgUrl + '" alt="QR" style="margin:15px auto;display:block" />' +
                        '<p style="font-size:11px;color:#999;word-break:break-all;max-width:380px;margin:10px auto">' + data.url + '</p>' +
                        '</body></html>'
                    );
                } else if (data.error) {
                    alert(data.error);
                }
            }).catch(function(err){
                console.error(err);
                alert('Error generando el QR.');
            });
        });
    });

    // Polling: actualizar estado de asistencia cada 5 segundos
    (function startPolling(){
        var scheduleId = '{{ $schedule->id }}';
        var classDate = '{{ $classDate }}';
        function poll(){
            fetch('{{ url('maestro/schedule') }}/' + encodeURIComponent(scheduleId) + '/attendances-json?date=' + encodeURIComponent(classDate), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            }).then(function(resp){
                if (!resp.ok) throw resp;
                return resp.json();
            }).then(function(json){
                if (json.attendances) {
                    Object.keys(json.attendances).forEach(function(regId){
                        var info = json.attendances[regId];
                        var radioPresent = document.querySelector('input[name="attendance[' + regId + ']"][value="1"]');
                        var radioAbsent = document.querySelector('input[name="attendance[' + regId + ']"][value="0"]');
                        if (info.attended) {
                            if (radioPresent && !radioPresent.checked) {
                                radioPresent.checked = true;
                            }
                        }

                        // Update marker display
                        var markerEl = document.getElementById('marker-' + regId);
                        if (markerEl) {
                            if (info.attended) {
                                var via = info.marked_via ? info.marked_via.toUpperCase() : 'QR';
                                var who = info.marked_by_name ? (' por ' + info.marked_by_name) : '';
                                var time = info.marked_at_local ? (' • ' + info.marked_at_local) : (info.marked_at ? (' • ' + info.marked_at + (info.marked_at_tz ? ' (' + info.marked_at_tz + ')' : '') ) : '');
                                markerEl.innerText = 'Presente — ' + via + who + time;
                                markerEl.className = 'text-xs text-green-700 font-medium';
                            } else {
                                markerEl.innerText = 'No registrado';
                                markerEl.className = 'text-xs text-gray-500';
                            }
                        }

                        // Update lesson column
                        var lessonEl = document.getElementById('lesson-' + regId);
                        if (lessonEl) {
                            if (info.lesson_order && info.lesson_title) {
                                lessonEl.innerHTML = '<span class="text-gray-700"><span class="text-blue-600 font-semibold">#' + info.lesson_order + '</span> ' + info.lesson_title + '</span>';
                            } else if (!info.lesson_id) {
                                lessonEl.innerHTML = '<span class="text-gray-400 italic text-xs">Sin avance registrado</span>';
                            }
                        }
                    });
                }
            }).catch(function(err){
                console.debug('Polling error', err);
            }).finally(function(){
                setTimeout(poll, 5000);
            });
        }
        poll();
    })();
});
</script>
@endpush
