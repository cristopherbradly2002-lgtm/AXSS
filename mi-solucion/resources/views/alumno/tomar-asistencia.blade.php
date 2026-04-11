@extends('layouts.app')

@section('title', 'Tomar Asistencia')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-blue-900">Tomar Asistencia</h2>
    <p class="text-gray-500 text-sm mt-1">Escanea el QR que te proporcionó tu profesor.</p>
</div>

<div class="bg-white rounded-xl shadow p-6">
    <div id="reader" style="width:100%;max-width:480px;margin:0 auto"></div>
    <div id="file-reader" style="display:none"></div>

    <div class="mt-4 text-center">
        <select id="camera-select" class="border rounded px-2 py-1 text-sm mb-2"></select>
        <div>
            <button id="start-scan" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded">Iniciar cámara</button>
            <button id="stop-scan" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded ml-2">Detener</button>
        </div>
        <div id="scan-status" class="text-sm text-gray-600 mt-2"></div>
    </div>

        <div class="mt-6">
        <label class="block text-sm font-medium text-gray-700">¿No puedes usar la cámara?</label>
        <input id="manual-url" type="text" placeholder="Pega aquí el enlace del QR" class="mt-2 w-full border rounded px-3 py-2">
        <div class="mt-2 flex gap-2">
            <button id="open-manual" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Abrir enlace</button>
            <label id="file-label" class="bg-gray-200 rounded px-3 py-1 text-sm cursor-pointer">
                Tomar foto / Abrir cámara
                <input id="file-input" type="file" accept="image/*" capture="environment" style="display:none">
            </label>
            <button id="open-photo-fallback" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">Abrir cámara (fallback)</button>
        </div>
        <div id="result-message" class="mt-3 text-center text-sm font-medium"></div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var html5QrCode;
    var startBtn = document.getElementById('start-scan');
    var stopBtn = document.getElementById('stop-scan');
    var readerDiv = document.getElementById('reader');
    var manualInput = document.getElementById('manual-url');
    var openManual = document.getElementById('open-manual');

    startBtn.addEventListener('click', async function(){
        try {
            startBtn.disabled = true;
            document.getElementById('scan-status').innerText = 'Solicitando permiso de cámara...';

            // Comprobar soporte de getUserMedia
            var supports_getusermedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
            if (!supports_getusermedia) {
                document.getElementById('scan-status').innerText = 'Tu navegador no permite acceso a la cámara desde esta página. Prueba con Chrome/Firefox en HTTPS o pega el enlace manualmente.';
                startBtn.disabled = false;
                return;
            }

            // Pedir permiso explícito para ayudar en móviles
            await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            let cameras = [];
            try {
                cameras = await Html5Qrcode.getCameras();
            } catch (e) {
                console.warn('getCameras falló', e);
            }
            if (!cameras || cameras.length === 0) {
                document.getElementById('scan-status').innerText = 'No se detectaron cámaras.';
                startBtn.disabled = false;
                return;
            }

            // Llenar selector de cámaras
            const sel = document.getElementById('camera-select');
            sel.innerHTML = '';
            cameras.forEach((cam, idx) => {
                const opt = document.createElement('option');
                opt.value = cam.id;
                opt.text = cam.label || ('Cámara ' + (idx+1));
                sel.appendChild(opt);
            });

            const chosen = sel.value || cameras[0].id;
            document.getElementById('scan-status').innerText = 'Escaneando...';

            await html5QrCode.start(
                chosen,
                { fps: 10, qrbox: 250 },
                qrCodeMessage => {
                            // qrCodeMessage contiene la URL firmada
                            // Hacemos fetch en background para marcar asistencia y mostrar confirmación
                            markAttendance(qrCodeMessage);
                },
                errorMessage => {
                    // mostrar error leve
                    console.debug('Decode error', errorMessage);
                }
            );
        } catch (err) {
            console.error(err);
            console.error('start camera failed', err);
            // If getUserMedia failed due to insecure origin or other camera policy,
            // fall back to opening the file input with capture attribute (mobile cameras).
            var fileInput = document.getElementById('file-input');
            if (fileInput) {
                try {
                    fileInput.click();
                    document.getElementById('scan-status').innerText = 'No se puede acceder a la cámara en este origen. Abriendo fallback de foto...';
                } catch (e) {
                    alert('Error al iniciar la cámara: ' + (err.message || err));
                    document.getElementById('scan-status').innerText = 'Error: ' + (err.message || err);
                }
            } else {
                alert('Error al iniciar la cámara: ' + (err.message || err));
                document.getElementById('scan-status').innerText = 'Error: ' + (err.message || err);
            }
        } finally {
            startBtn.disabled = false;
        }
    });

    stopBtn.addEventListener('click', function(){
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                // cleared
            }).catch(err => console.error(err));
        }
    });

    openManual.addEventListener('click', function(){
        var url = manualInput.value.trim();
        if (!url) { alert('Pega la URL del QR.'); return; }
        markAttendance(url);
    });

    // Manejar subida de imagen y decodificación
    var fileInput = document.getElementById('file-input');
    fileInput.addEventListener('change', function(e){
        var file = e.target.files[0];
        if (!file) return;
        document.getElementById('scan-status').innerText = 'Procesando imagen...';
        document.getElementById('result-message').innerText = '';

        // Stop camera if running to free resources
        var stopPromise = Promise.resolve();
        if (html5QrCode) {
            stopPromise = html5QrCode.stop().catch(function(){ /* ignore if not running */ });
        }

        stopPromise.then(function() {
            if (typeof Html5Qrcode === 'undefined') {
                throw new Error('La librería del escáner QR no se cargó. Recarga la página.');
            }
            // Use a separate hidden div to avoid DOM conflicts with the camera reader
            var fileReaderDiv = document.getElementById('file-reader');
            fileReaderDiv.innerHTML = '';
            var fileScanner = new Html5Qrcode('file-reader');
            return fileScanner.scanFile(file, /* showImage */ false).then(function(decodedText) {
                console.log('QR decoded from photo:', decodedText);
                document.getElementById('scan-status').innerText = 'QR detectado, enviando...';
                markAttendance(decodedText);
                try { fileScanner.clear(); } catch(x) {}
            }).catch(function(err) {
                console.error('scanFile failed', err);
                document.getElementById('scan-status').innerText = '';
                document.getElementById('result-message').innerText = 'No se pudo leer el QR de la imagen. Intenta con otra foto más nítida o pega el enlace manualmente.';
                document.getElementById('result-message').className = 'mt-3 text-center text-sm font-medium text-red-600';
                try { fileScanner.clear(); } catch(x) {}
            });
        }).catch(function(err) {
            console.error('File scan error:', err);
            document.getElementById('scan-status').innerText = '';
            document.getElementById('result-message').innerText = err.message || 'Error procesando la imagen.';
            document.getElementById('result-message').className = 'mt-3 text-center text-sm font-medium text-red-600';
        });

        // Reset input so the same file can be re-selected
        fileInput.value = '';
    });

    // Explicit fallback button to open file input (camera on mobile)
    var openPhotoFallback = document.getElementById('open-photo-fallback');
    if (openPhotoFallback) {
        openPhotoFallback.addEventListener('click', function(){
            var fileInput = document.getElementById('file-input');
            if (fileInput) fileInput.click();
        });
    }

    // Mark attendance via fetch, show result inside page
    function markAttendance(qrUrl) {
        // Ensure we have a string URL
        if (typeof qrUrl !== 'string' || !qrUrl.trim()) {
            document.getElementById('result-message').innerText = 'No se obtuvo una URL válida del QR.';
            document.getElementById('result-message').className = 'mt-3 text-center text-sm font-medium text-red-600';
            return;
        }

        document.getElementById('result-message').innerText = 'Enviando asistencia...';
        document.getElementById('result-message').className = 'mt-3 text-center text-sm font-medium text-blue-600';

        // Parse the QR URL and POST its query params to our local endpoint
        try {
            var parser = document.createElement('a');
            parser.href = qrUrl;
            var search = parser.search || (qrUrl.indexOf('?') !== -1 ? qrUrl.slice(qrUrl.indexOf('?')) : '');
            var params = new URLSearchParams(search);
            var body = {};
            // include full URL so server can validate signature exactly as encoded
            body.full_url = qrUrl;
            ['schedule','lesson','date','student','expires','signature'].forEach(function(k){
                if (params.has(k)) body[k] = params.get(k);
            });
            // add client timezone so backend can store/display it
            try {
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (tz) body.timezone = tz;
            } catch(e) { /* ignore */ }

            // Send to local endpoint
            fetch('{{ route('attendance.mark-local') }}', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(body)
            }).then(function(resp){
                if (!resp.ok) {
                    // try read body for more details
                    return resp.text().then(function(text){
                        throw new Error('HTTP ' + resp.status + ' - ' + text);
                    });
                }
                return resp.json();
            }).then(function(json){
                if (json && json.success) {
                    document.getElementById('result-message').innerText = json.message || 'Asistencia registrada.';
                    document.getElementById('result-message').className = 'mt-3 text-center text-sm font-bold text-green-700 bg-green-50 border border-green-300 rounded-lg p-3';
                    document.getElementById('scan-status').innerText = '';
                } else {
                    document.getElementById('result-message').innerText = (json && json.message) ? json.message : 'Error registrando asistencia.';
                    document.getElementById('result-message').className = 'mt-3 text-center text-sm font-medium text-red-600';
                }
            }).catch(function(err){
                console.error('markAttendance error', err);
                document.getElementById('result-message').innerText = 'Error comunicando con el servidor: ' + (err.message || err);
                document.getElementById('result-message').className = 'mt-3 text-center text-sm font-medium text-red-600';
            }).finally(function(){
                // stop camera if running
                if (html5QrCode) {
                    html5QrCode.stop().catch(function(){})
                }
            });
        } catch (e) {
            console.error(e);
            document.getElementById('result-message').innerText = 'Formato de QR inválido.';
        }
    }
});
</script>
@endpush
