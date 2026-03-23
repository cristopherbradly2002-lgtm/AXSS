<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRegistration;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaestroController extends Controller
{
    /**
     * Panel principal del maestro: muestra cursos asignados, horarios, salones y cantidad de alumnos.
     */
    public function dashboard()
    {
        $teacher = Auth::user();

        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('active', true)
            ->with(['course', 'classroom'])
            ->get()
            ->map(function ($schedule) {
                $today = Carbon::today()->format('Y-m-d');
                $schedule->students_count = ClassRegistration::where('schedule_id', $schedule->id)
                    ->where('class_date', $today)
                    ->where('status', 'registered')
                    ->count();
                return $schedule;
            });

        return view('maestro.dashboard', compact('schedules'));
    }

    /**
     * Lista de alumnos inscritos en una clase con su lección actual.
     */
    public function listaAlumnos($scheduleId, Request $request)
    {
        $schedule = Schedule::with(['course.lessons', 'classroom'])
            ->findOrFail($scheduleId);

        $classDate = $request->get('class_date', Carbon::today()->format('Y-m-d'));

        $registrations = ClassRegistration::where('schedule_id', $scheduleId)
            ->where('class_date', $classDate)
            ->where('status', 'registered')
            ->with('user')
            ->get()
            ->map(function ($registration) use ($schedule) {
                $pivot = $registration->user->courses()
                    ->where('course_id', $schedule->course_id)
                    ->first();

                $registration->current_lesson = $pivot ? $pivot->pivot->current_lesson : 0;
                return $registration;
            });

        $lessons = $schedule->course->lessons;

        return view('maestro.lista-alumnos', compact('schedule', 'registrations', 'lessons', 'classDate'));
    }

    /**
     * Guarda la asistencia del alumno y el tema impartido.
     * Actualiza el avance del curso.
     */
    public function guardarAsistencia(Request $request)
    {
        $request->validate([
            'class_registration_id' => 'required|exists:class_registrations,id',
            'lesson_id'             => 'required|exists:lessons,id',
            'attended'              => 'required|boolean',
        ]);

        $registration = ClassRegistration::with(['schedule.course', 'user'])
            ->findOrFail($request->class_registration_id);

        $attendance = Attendance::updateOrCreate(
            [
                'class_registration_id' => $registration->id,
                'user_id'               => $registration->user_id,
                'schedule_id'           => $registration->schedule_id,
                'class_date'            => $registration->class_date,
            ],
            [
                'lesson_id' => $request->lesson_id,
                'attended'  => $request->attended,
                'notes'     => $request->notes,
            ]
        );

        // Si asistió, actualizar el avance del alumno en el curso
        if ($request->attended) {
            $registration->user->courses()->updateExistingPivot(
                $registration->schedule->course_id,
                ['current_lesson' => $request->lesson_id]
            );
        }

        return back()->with('success', 'Asistencia registrada correctamente.');
    }
}
