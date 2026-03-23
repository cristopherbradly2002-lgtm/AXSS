<?php

namespace App\Http\Controllers;

use App\Models\ClassRegistration;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumnoController extends Controller
{
    /**
     * Muestra los cursos del alumno autenticado.
     */
    public function misCursos()
    {
        $user = Auth::user();

        $courses = $user->courses()->with('lessons')->get()->map(function ($course) {
            $totalLessons = $course->lessons->count();
            $currentLesson = $course->pivot->current_lesson;

            return [
                'course'           => $course,
                'total_lessons'    => $totalLessons,
                'current_lesson'   => $currentLesson,
                'remaining'        => max(0, $totalLessons - $currentLesson),
                'progress_percent' => $totalLessons > 0
                    ? round(($currentLesson / $totalLessons) * 100, 1)
                    : 0,
            ];
        });

        return view('alumno.mis-cursos', compact('courses'));
    }

    /**
     * Muestra los horarios disponibles para un curso en formato calendario.
     */
    public function horarios($courseId)
    {
        $schedules = Schedule::where('course_id', $courseId)
            ->where('active', true)
            ->with(['classroom', 'teacher'])
            ->get();

        return view('alumno.horarios', compact('schedules', 'courseId'));
    }

    /**
     * Registra al alumno en una clase específica.
     *
     * Valida:
     * 1. Regla de 24 horas: la clase debe registrarse con al menos 24 horas de anticipación.
     * 2. Regla de cupo: cada salón tiene un cupo de 2 a 4 alumnos por clase.
     */
    public function registrarClase(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'class_date'  => 'required|date|after_or_equal:today',
        ]);

        $user      = Auth::user();
        $schedule  = Schedule::with('classroom')->findOrFail($request->schedule_id);
        $classDate = Carbon::parse($request->class_date);

        // --- Regla de 24 horas ---
        $classDateTime = Carbon::parse(
            $classDate->format('Y-m-d') . ' ' . $schedule->start_time
        );

        if (Carbon::now()->diffInHours($classDateTime, false) < 24) {
            return back()->withErrors([
                'class_date' => 'Recuerda: para asignarte un horario debes hacerlo con 24 horas de anticipación',
            ]);
        }

        // --- Regla de cupo (máximo = capacidad del salón, entre 2 y 4) ---
        $registeredCount = ClassRegistration::where('schedule_id', $schedule->id)
            ->where('class_date', $classDate->format('Y-m-d'))
            ->where('status', 'registered')
            ->count();

        $capacity = $schedule->classroom->capacity; // valor entre 2 y 4

        if ($registeredCount >= $capacity) {
            return back()->withErrors([
                'schedule_id' => 'Lo sentimos, el cupo para esta clase está lleno. No hay lugares disponibles.',
            ]);
        }

        // --- Verificar que el alumno no esté ya registrado ---
        $alreadyRegistered = ClassRegistration::where('schedule_id', $schedule->id)
            ->where('user_id', $user->id)
            ->where('class_date', $classDate->format('Y-m-d'))
            ->where('status', 'registered')
            ->exists();

        if ($alreadyRegistered) {
            return back()->withErrors([
                'schedule_id' => 'Ya estás registrado en esta clase para la fecha seleccionada.',
            ]);
        }

        // --- Crear el registro ---
        ClassRegistration::create([
            'schedule_id' => $schedule->id,
            'user_id'     => $user->id,
            'class_date'  => $classDate->format('Y-m-d'),
            'status'      => 'registered',
        ]);

        return back()->with('success', 'Te has registrado exitosamente en la clase.');
    }
}
