<?php

namespace App\Http\Controllers;

use App\Models\ClassRegistration;
use App\Models\Course;
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
     * Muestra el detalle de un curso: progreso, temario y salones disponibles.
     */
    public function detalleCurso($courseId)
    {
        $user   = Auth::user();
        $course = Course::with('lessons')->findOrFail($courseId);

        // Verifica que el alumno esté inscrito en este curso
        $pivot = $user->courses()->where('course_id', $courseId)->first();
        abort_if(!$pivot, 403);

        $currentLesson  = $pivot->pivot->current_lesson;
        $totalLessons   = $course->lessons->count();
        $remaining      = max(0, $totalLessons - $currentLesson);
        $progressPercent = $totalLessons > 0
            ? round(($currentLesson / $totalLessons) * 100, 1)
            : 0;

        // Salones y horarios enriquecidos (misma lógica que horarios())
        $dayMap = [
            'Lunes'     => Carbon::MONDAY,
            'Martes'    => Carbon::TUESDAY,
            'Miércoles' => Carbon::WEDNESDAY,
            'Jueves'    => Carbon::THURSDAY,
            'Viernes'   => Carbon::FRIDAY,
            'Sábado'    => Carbon::SATURDAY,
            'Domingo'   => Carbon::SUNDAY,
        ];

        $schedules = Schedule::where('course_id', $courseId)
            ->where('active', true)
            ->with(['classroom', 'teacher'])
            ->get()
            ->map(function ($schedule) use ($dayMap) {
                $dayNumber = $dayMap[$schedule->day_of_week] ?? Carbon::MONDAY;
                $now       = Carbon::now();
                $nextDate  = $now->copy()->next($dayNumber);

                $classDateTime = Carbon::parse($nextDate->format('Y-m-d') . ' ' . $schedule->start_time);
                if ($classDateTime->diffInHours($now, false) > -24) {
                    $nextDate      = $nextDate->addWeek();
                }

                $registeredCount = ClassRegistration::where('schedule_id', $schedule->id)
                    ->where('class_date', $nextDate->format('Y-m-d'))
                    ->where('status', 'registered')
                    ->count();

                $schedule->next_date        = $nextDate->format('Y-m-d');
                $schedule->registered_count = $registeredCount;
                $schedule->available        = $registeredCount < $schedule->classroom->capacity;

                return $schedule;
            });

        return view('alumno.detalle-curso', compact(
            'course', 'currentLesson', 'totalLessons', 'remaining',
            'progressPercent', 'schedules'
        ));
    }

    /**
     * Muestra los horarios disponibles para un curso en formato calendario.
     * Enriquece cada horario con la próxima fecha disponible y conteo de cupo.
     */
    public function horarios($courseId)
    {
        $dayMap = [
            'Lunes'     => Carbon::MONDAY,
            'Martes'    => Carbon::TUESDAY,
            'Miércoles' => Carbon::WEDNESDAY,
            'Jueves'    => Carbon::THURSDAY,
            'Viernes'   => Carbon::FRIDAY,
            'Sábado'    => Carbon::SATURDAY,
            'Domingo'   => Carbon::SUNDAY,
        ];

        $schedules = Schedule::where('course_id', $courseId)
            ->where('active', true)
            ->with(['classroom', 'teacher'])
            ->get()
            ->map(function ($schedule) use ($dayMap) {
                $dayNumber = $dayMap[$schedule->day_of_week] ?? Carbon::MONDAY;
                $now       = Carbon::now();

                // Próxima ocurrencia del día de la semana
                $nextDate = $now->copy()->next($dayNumber);

                // Si esa ocurrencia no cumple la regla de 24 horas, avanzar una semana
                $classDateTime = Carbon::parse($nextDate->format('Y-m-d') . ' ' . $schedule->start_time);
                if ($classDateTime->diffInHours($now, false) > -24) {
                    $nextDate      = $nextDate->addWeek();
                    $classDateTime = Carbon::parse($nextDate->format('Y-m-d') . ' ' . $schedule->start_time);
                }

                $registeredCount = ClassRegistration::where('schedule_id', $schedule->id)
                    ->where('class_date', $nextDate->format('Y-m-d'))
                    ->where('status', 'registered')
                    ->count();

                $schedule->next_date       = $nextDate->format('Y-m-d');
                $schedule->registered_count = $registeredCount;
                $schedule->available        = $registeredCount < $schedule->classroom->capacity;

                return $schedule;
            });

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

        return back()->with('success', 'El usuario se ha inscrito a la clase correctamente.');
    }
}
