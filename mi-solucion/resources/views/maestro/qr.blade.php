@extends('layouts.app')

@section('content')
<div class="container">
    <h2>QR de asistencia — {{ $lesson->title }}</h2>
    <p>Fecha: {{ $date }} — Expira: {{ $expiresAt->toDateTimeString() }}</p>

    <div style="margin:20px 0">
        <img src="{{ $qrSrc }}" alt="QR de asistencia" />
    </div>

    <p>Si algún alumno no puede escanear, puede abrir este enlace (firmado y temporal):</p>
    <p><a href="{{ $signedUrl }}">{{ $signedUrl }}</a></p>
</div>
@endsection
