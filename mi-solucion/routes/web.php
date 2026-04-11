<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Raíz: redirige según rol o muestra login
Route::get('/', function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'maestro' => redirect()->route('maestro.dashboard'),
            default   => redirect()->route('alumno.mis-cursos'),
        };
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
    // Página para que el alumno escanee un QR y marque su asistencia
    Route::get('/tomar-asistencia', [\App\Http\Controllers\AttendanceController::class, 'studentScanner'])->name('tomar-asistencia');
    Route::post('/registrar-clase', [AlumnoController::class, 'registrarClase'])->name('registrar-clase');
});

// Rutas del Maestro
Route::middleware('maestro')->prefix('maestro')->name('maestro.')->group(function () {
    Route::get('/dashboard', [MaestroController::class, 'dashboard'])->name('dashboard');
    Route::get('/lista-alumnos/{scheduleId}', [MaestroController::class, 'listaAlumnos'])->name('lista-alumnos');
    Route::post('/guardar-clase', [MaestroController::class, 'guardarClase'])->name('guardar-clase');
    // Mostrar QR para que los alumnos registren asistencia escaneando
    Route::get('/schedule/{scheduleId}/lesson/{lessonId}/qr', [AttendanceController::class, 'showQr'])->name('lesson.qr');
    // Generar QR específico por alumno (invocado por AJAX desde la vista)
    Route::post('/generate-student-qr', [AttendanceController::class, 'generateStudentQr'])->name('generate-student-qr');
    // Obtener asistencias en JSON para polling en la vista del maestro
    Route::get('/schedule/{scheduleId}/attendances-json', [\App\Http\Controllers\AttendanceController::class, 'attendancesForSchedule'])->name('attendances.json');
});

// ─── Rutas del Administrador ─────────────────────────────────
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Users CRUD
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

    // Courses CRUD
    Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
    Route::get('/courses/create', [AdminController::class, 'createCourse'])->name('courses.create');
    Route::post('/courses', [AdminController::class, 'storeCourse'])->name('courses.store');
    Route::get('/courses/{id}/edit', [AdminController::class, 'editCourse'])->name('courses.edit');
    Route::put('/courses/{id}', [AdminController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{id}', [AdminController::class, 'deleteCourse'])->name('courses.delete');

    // Classrooms CRUD
    Route::get('/classrooms', [AdminController::class, 'classrooms'])->name('classrooms');
    Route::get('/classrooms/create', [AdminController::class, 'createClassroom'])->name('classrooms.create');
    Route::post('/classrooms', [AdminController::class, 'storeClassroom'])->name('classrooms.store');
    Route::get('/classrooms/{id}/edit', [AdminController::class, 'editClassroom'])->name('classrooms.edit');
    Route::put('/classrooms/{id}', [AdminController::class, 'updateClassroom'])->name('classrooms.update');
    Route::delete('/classrooms/{id}', [AdminController::class, 'deleteClassroom'])->name('classrooms.delete');

    // Schedules CRUD
    Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
    Route::get('/schedules/create', [AdminController::class, 'createSchedule'])->name('schedules.create');
    Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
    Route::get('/schedules/{id}/edit', [AdminController::class, 'editSchedule'])->name('schedules.edit');
    Route::put('/schedules/{id}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/schedules/{id}', [AdminController::class, 'deleteSchedule'])->name('schedules.delete');

    // Attendance Report
    Route::get('/attendance-report', [AdminController::class, 'attendanceReport'])->name('attendance-report');

    // Impersonate
    Route::get('/impersonate/{id}', [AdminController::class, 'impersonate'])->name('impersonate');
    Route::get('/stop-impersonate', [AdminController::class, 'stopImpersonate'])->name('stop-impersonate');
});

// Ruta firmada para que el alumno marque asistencia al escanear el QR
Route::get('/attendance/mark', [AttendanceController::class, 'markViaQr'])
    ->name('attendance.mark')
    ->middleware(['signed']);

// Endpoint local para que el cliente envíe los query params del QR (evita CORS)
Route::post('/attendance/mark-local', [AttendanceController::class, 'markLocal'])->name('attendance.mark-local');
