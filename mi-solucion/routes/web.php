<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaestroController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Raíz: redirige según rol o muestra login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect(
            Auth::user()->isMaestro()
                ? route('maestro.dashboard')
                : route('alumno.mis-cursos')
        );
    }
    return redirect()->route('login');
});

// Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas del Alumno
Route::middleware('alumno')->prefix('alumno')->name('alumno.')->group(function () {
    Route::get('/mis-cursos', [AlumnoController::class, 'misCursos'])->name('mis-cursos');
    Route::get('/detalle-curso/{courseId}', [AlumnoController::class, 'detalleCurso'])->name('detalle-curso');
    Route::get('/horarios/{courseId}', [AlumnoController::class, 'horarios'])->name('horarios');
    Route::post('/registrar-clase', [AlumnoController::class, 'registrarClase'])->name('registrar-clase');
});

// Rutas del Maestro
Route::middleware('maestro')->prefix('maestro')->name('maestro.')->group(function () {
    Route::get('/dashboard', [MaestroController::class, 'dashboard'])->name('dashboard');
    Route::get('/lista-alumnos/{scheduleId}', [MaestroController::class, 'listaAlumnos'])->name('lista-alumnos');
    Route::post('/guardar-clase', [MaestroController::class, 'guardarClase'])->name('guardar-clase');
});
