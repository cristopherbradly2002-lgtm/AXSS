@extends('layouts.app')

@section('title', 'Marcando asistencia')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-blue-900">Marcando asistencia...</h2>
        <p class="text-gray-500 text-sm mt-1">Capturando información del dispositivo.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6 text-center">
        <div id="marker-status" class="text-gray-700">Espere un momento...</div>
        <div id="marker-detail" class="text-sm text-gray-500 mt-2"></div>
        <div id="marker-actions" class="mt-4"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var status = document.getElementById('marker-status');
    var detail = document.getElementById('marker-detail');
    var actions = document.getElementById('marker-actions');
    var fullUrl = @json($fullUrl);

    function sendMark() {
        status.innerText = 'Detectando zona horaria...';
        var tz = null;
        try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone; } catch(e) {}
        detail.innerText = tz ? ('Zona horaria: ' + tz) : 'No se pudo detectar zona horaria.';

        status.innerText = 'Enviando datos al servidor...';
        fetch('{{ route("attendance.mark-local") }}', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ full_url: fullUrl, timezone: tz })
        })
        .then(function(resp){ return resp.json(); })
        .then(function(json){
            if (json && json.success) {
                status.innerText = json.message || 'Asistencia registrada correctamente.';
                status.className = 'text-green-700 font-bold';
                detail.innerText = '';
                actions.innerHTML = '<a href="/" class="bg-blue-700 text-white px-4 py-2 rounded inline-block mt-2">Volver</a>';
            } else {
                status.innerText = (json && json.message) ? json.message : 'Error registrando asistencia.';
                status.className = 'text-red-600 font-bold';
                showRetry();
            }
        })
        .catch(function(err){
            status.innerText = 'Error comunicando con el servidor.';
            status.className = 'text-red-600 font-bold';
            detail.innerText = err.message || '';
            showRetry();
        });
    }

    function showRetry() {
        actions.innerHTML = '<button id="retry-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded mt-2">Reintentar</button>';
        document.getElementById('retry-btn').addEventListener('click', sendMark);
    }

    sendMark();
});
</script>
@endpush
