<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRegistration;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AttendanceController extends Controller
{
    /**
     * Mostrar QR para una clase (solo profesor propietario del horario).
     */
    public function showQr($scheduleId, $lessonId, Request $request)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $user = Auth::user();

        if (!$user || ($schedule->teacher_id !== $user->id && !$user->isAdmin())) {
            abort(403);
        }

        $lesson = Lesson::findOrFail($lessonId);
        $date = $request->query('date', Carbon::today()->toDateString());

        $signedUrl = URL::temporarySignedRoute(
            'attendance.mark',
            now()->addMinutes(60),
            [
                'schedule' => $schedule->id,
                'lesson'   => $lesson->id,
                'date'     => $date,
            ]
        );

        $qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($signedUrl);

        return view('maestro.qr', [
            'schedule'  => $schedule,
            'lesson'    => $lesson,
            'date'      => $date,
            'signedUrl' => $signedUrl,
            'qrSrc'     => $qrSrc,
            'expiresAt' => now()->addMinutes(60),
        ]);
    }

    /**
     * Marca asistencia cuando el alumno abre el QR directamente en el navegador.
     * La ruta tiene middleware 'signed' que valida la firma.
     */
    public function markViaQr(Request $request)
    {
        // Si el alumno abrió el link directamente (no AJAX), mostrar página
        // intermedia que captura la zona horaria y hace POST a mark-local.
        if (!$request->has('timezone') && !$request->expectsJson()) {
            return view('attendance.mark-js', [
                'fullUrl' => $request->fullUrl(),
            ]);
        }

        $scheduleId = $request->query('schedule');
        $lessonId   = $request->query('lesson');
        $date       = $request->query('date', Carbon::today()->toDateString());
        $studentId  = $request->query('student');
        $timezone   = $request->input('timezone');

        if (!$studentId) {
            $user = $request->user();
            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Debes iniciar sesión para registrar asistencia.');
            }
            $studentId = $user->id;
        }

        return $this->recordAttendance($scheduleId, $lessonId, $date, $studentId, $timezone, $request);
    }

    /**
     * Genera URL firmada por alumno específico (AJAX, solo profesor).
     */
    public function generateStudentQr(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'lesson_id'   => 'required|exists:lessons,id',
            'student_id'  => 'required|exists:users,id',
            'date'        => 'nullable|date',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);
        $user = Auth::user();

        if (!$user || ($schedule->teacher_id !== $user->id && !$user->isAdmin())) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $date = $request->input('date', Carbon::today()->toDateString());

        $signedUrl = URL::temporarySignedRoute(
            'attendance.mark',
            now()->addMinutes(60),
            [
                'schedule' => $schedule->id,
                'lesson'   => $request->lesson_id,
                'date'     => $date,
                'student'  => $request->student_id,
            ]
        );

        return response()->json(['url' => $signedUrl]);
    }

    /**
     * JSON polling: devuelve asistencias para un schedule y fecha (solo profesor).
     */
    public function attendancesForSchedule(Request $request, $scheduleId)
    {
        $user = Auth::user();
        $schedule = Schedule::findOrFail($scheduleId);

        if (!$user || ($schedule->teacher_id !== $user->id && !$user->isAdmin())) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $date = $request->query('date', Carbon::today()->toDateString());

        $registrations = ClassRegistration::where('schedule_id', $scheduleId)
            ->whereDate('class_date', $date)
            ->get();

        $attendances = Attendance::where('schedule_id', $scheduleId)
            ->whereDate('class_date', $date)
            ->latest('updated_at')
            ->get()
            ->unique('class_registration_id')
            ->keyBy('class_registration_id');

        $result = [];
        foreach ($registrations as $reg) {
            $a = $attendances->get($reg->id);
            if ($a) {
                $markedByUser = $a->marked_by ? User::find($a->marked_by) : null;
                $markedAtLocal = null;

                if ($a->marked_at) {
                    try {
                        $markedAtLocal = $a->marked_at_tz
                            ? Carbon::parse($a->marked_at)->setTimezone($a->marked_at_tz)->toDateTimeString()
                            : Carbon::parse($a->marked_at)->toDateTimeString();
                    } catch (\Exception $e) {
                        $markedAtLocal = (string) $a->marked_at;
                    }
                }

                // Obtener info de la lección para el polling
                $lessonObj = $a->lesson_id ? Lesson::find($a->lesson_id) : null;

                $result[$reg->id] = [
                    'attended'        => (bool) $a->attended,
                    'marked_via'      => $a->marked_via,
                    'marked_by'       => $a->marked_by,
                    'marked_by_name'  => $markedByUser?->name,
                    'marked_at'       => $a->marked_at ? Carbon::parse($a->marked_at)->toDateTimeString() : null,
                    'marked_at_tz'    => $a->marked_at_tz,
                    'marked_at_local' => $markedAtLocal,
                    'lesson_id'       => $a->lesson_id,
                    'lesson_order'    => $lessonObj?->order,
                    'lesson_title'    => $lessonObj?->title,
                ];
            } else {
                $result[$reg->id] = [
                    'attended'       => false,
                    'marked_via'     => null,
                    'marked_by'      => null,
                    'marked_by_name' => null,
                    'marked_at'      => null,
                    'lesson_id'      => null,
                    'lesson_order'   => null,
                    'lesson_title'   => null,
                ];
            }
        }

        return response()->json(['attendances' => $result]);
    }

    /**
     * Endpoint para que el escáner del alumno envíe la URL completa del QR.
     * Valida la firma de la URL y registra la asistencia.
     */
    public function markLocal(Request $request)
    {
        $fullUrl = $request->input('full_url');

        if (!$fullUrl) {
            return response()->json([
                'success' => false,
                'message' => 'No se proporcionó la URL del QR.',
            ], 422);
        }

        Log::info('markLocal called', ['full_url' => $fullUrl]);

        // Crear un request falso a partir de la URL escaneada para validar la firma.
        // IMPORTANTE: $absolute = true porque la firma fue generada sobre la URL absoluta.
        $fakeRequest = Request::create($fullUrl, 'GET');

        if (!URL::hasValidSignature($fakeRequest, true)) {
            // Desglosar el error para el log
            $hasCorrectSig = URL::hasCorrectSignature($fakeRequest, true);
            Log::warning('markLocal: signature validation failed', [
                'full_url'      => $fullUrl,
                'correct_sig'   => $hasCorrectSig ? 'yes' : 'no',
                'expired'       => $hasCorrectSig ? 'yes (expired)' : 'n/a',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Enlace inválido o expirado.',
            ], 403);
        }

        // Extraer parámetros de la URL validada
        parse_str(parse_url($fullUrl, PHP_URL_QUERY) ?? '', $query);

        $scheduleId = $query['schedule'] ?? null;
        $lessonId   = $query['lesson'] ?? null;
        $date       = $query['date'] ?? Carbon::today()->toDateString();
        $studentId  = $query['student'] ?? null;
        $timezone   = $request->input('timezone');

        if (!$studentId) {
            $authUser = $request->user();
            if ($authUser) {
                $studentId = $authUser->id;
            }
        }

        if (!$scheduleId || !$lessonId || !$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros incompletos.',
            ], 422);
        }

        return $this->recordAttendance($scheduleId, $lessonId, $date, $studentId, $timezone, $request);
    }

    /**
     * Página del escáner QR del alumno.
     */
    public function studentScanner()
    {
        return view('alumno.tomar-asistencia');
    }

    /**
     * Lógica compartida para registrar asistencia y actualizar el pivot.
     */
    private function recordAttendance($scheduleId, $lessonId, $date, $studentId, $timezone, Request $request)
    {
        $classReg = ClassRegistration::where('user_id', $studentId)
            ->where('schedule_id', $scheduleId)
            ->whereDate('class_date', $date)
            ->first();

        if (!$classReg) {
            $msg = 'El alumno no está registrado para esta clase.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        $matchKeys = [
            ['class_registration_id', '=', $classReg->id],
            ['user_id', '=', $studentId],
            ['schedule_id', '=', $scheduleId],
            ['class_date', '=', $date],
        ];

        // Get the most-recently-updated record if duplicates exist
        $attendance = Attendance::where($matchKeys)
            ->latest('updated_at')
            ->first();

        // Remove stale duplicates
        if ($attendance) {
            Attendance::where($matchKeys)
                ->where('id', '!=', $attendance->id)
                ->delete();
        }

        $now = Carbon::now();
        $values = [
            'lesson_id'    => $lessonId,
            'attended'     => true,
            'marked_via'   => 'qr',
            'marked_by'    => $studentId,
            'marked_at'    => $now,
            'marked_at_tz' => $timezone,
        ];

        if ($attendance) {
            $attendance->forceFill($values)->save();
        } else {
            Attendance::create(array_merge([
                'class_registration_id' => $classReg->id,
                'user_id'               => $studentId,
                'schedule_id'           => $scheduleId,
                'class_date'            => $date,
            ], $values));
        }

        // Actualizar current_lesson en el pivot course_user
        try {
            $schedule = Schedule::find($scheduleId);
            if ($schedule) {
                $classReg->user->courses()->syncWithoutDetaching([
                    $schedule->course_id => ['current_lesson' => $lessonId],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update pivot', [
                'error'       => $e->getMessage(),
                'user_id'     => $studentId,
                'schedule_id' => $scheduleId,
            ]);
        }

        $msg = 'Asistencia registrada correctamente.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return view('attendance.marked', ['message' => $msg]);
    }
}
