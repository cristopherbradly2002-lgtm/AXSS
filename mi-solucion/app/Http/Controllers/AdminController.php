<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRegistration;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_users'       => User::count(),
            'total_admins'      => User::where('role', 'admin')->count(),
            'total_maestros'    => User::where('role', 'maestro')->count(),
            'total_alumnos'     => User::where('role', 'alumno')->count(),
            'total_courses'     => Course::count(),
            'active_courses'    => Course::where('active', true)->count(),
            'total_classrooms'  => Classroom::count(),
            'total_schedules'   => Schedule::where('active', true)->count(),
            'today_classes'     => ClassRegistration::whereDate('class_date', Carbon::today())->count(),
            'today_attendance'  => Attendance::whereDate('class_date', Carbon::today())->where('attended', true)->count(),
            'total_attendances' => Attendance::where('attended', true)->count(),
        ];

        // Recent activity — last 15 attendance marks
        $recentActivity = Attendance::with(['user', 'schedule.course', 'lesson'])
            ->whereNotNull('marked_at')
            ->orderByDesc('marked_at')
            ->limit(15)
            ->get();

        // Attendance rate per course
        $courseStats = Course::withCount(['schedules'])->where('active', true)->get()->map(function ($course) {
            $total = Attendance::whereHas('schedule', fn($q) => $q->where('course_id', $course->id))->count();
            $present = Attendance::whereHas('schedule', fn($q) => $q->where('course_id', $course->id))->where('attended', true)->count();
            $course->attendance_rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            $course->total_records = $total;
            return $course;
        });

        return view('admin.dashboard', compact('stats', 'recentActivity', 'courseStats'));
    }

    // ─── Users CRUD ──────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.form', ['user' => null, 'courses' => Course::where('active', true)->get()]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:admin,maestro,alumno',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        // Enroll in courses if alumno
        if ($request->filled('courses')) {
            foreach ($request->courses as $courseId) {
                $user->courses()->attach($courseId, ['current_lesson' => 0]);
            }
        }

        return redirect()->route('admin.users')->with('success', "Usuario «{$user->name}» creado.");
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $courses = Course::where('active', true)->get();
        $enrolledCourses = $user->courses()->pluck('course_id')->toArray();

        return view('admin.users.form', compact('user', 'courses', 'enrolledCourses'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:admin,maestro,alumno',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        // Sync course enrollments
        if ($request->has('courses')) {
            $existing = $user->courses()->pluck('current_lesson', 'course_id')->toArray();
            $syncData = [];
            foreach ($request->courses as $courseId) {
                $syncData[$courseId] = ['current_lesson' => $existing[$courseId] ?? 0];
            }
            $user->courses()->sync($syncData);
        } else {
            $user->courses()->detach();
        }

        return redirect()->route('admin.users')->with('success', "Usuario «{$user->name}» actualizado.");
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', "Usuario «{$user->name}» eliminado.");
    }

    // ─── Courses CRUD ────────────────────────────────────────

    public function courses()
    {
        $courses = Course::withCount(['lessons', 'users', 'schedules'])->orderBy('name')->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function createCourse()
    {
        return view('admin.courses.form', ['course' => null]);
    }

    public function storeCourse(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'active'        => 'boolean',
            'lessons'       => 'nullable|array',
            'lessons.*'     => 'string|max:255',
        ]);

        $course = Course::create([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'total_lessons' => count($data['lessons'] ?? []),
            'active'        => $request->boolean('active', true),
        ]);

        foreach (($data['lessons'] ?? []) as $i => $title) {
            if (trim($title)) {
                Lesson::create(['course_id' => $course->id, 'title' => trim($title), 'order' => $i + 1]);
            }
        }

        return redirect()->route('admin.courses')->with('success', "Curso «{$course->name}» creado.");
    }

    public function editCourse($id)
    {
        $course = Course::with('lessons')->findOrFail($id);
        return view('admin.courses.form', compact('course'));
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'active'        => 'boolean',
            'lessons'       => 'nullable|array',
            'lessons.*'     => 'string|max:255',
        ]);

        $course->update([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'active'        => $request->boolean('active', true),
        ]);

        // Rebuild lessons
        $course->lessons()->delete();
        $count = 0;
        foreach (($data['lessons'] ?? []) as $i => $title) {
            if (trim($title)) {
                Lesson::create(['course_id' => $course->id, 'title' => trim($title), 'order' => $i + 1]);
                $count++;
            }
        }
        $course->update(['total_lessons' => $count]);

        return redirect()->route('admin.courses')->with('success', "Curso «{$course->name}» actualizado.");
    }

    public function deleteCourse($id)
    {
        $course = Course::findOrFail($id);
        $course->lessons()->delete();
        $course->delete();
        return redirect()->route('admin.courses')->with('success', "Curso «{$course->name}» eliminado.");
    }

    // ─── Classrooms CRUD ─────────────────────────────────────

    public function classrooms()
    {
        $classrooms = Classroom::withCount('schedules')->orderBy('name')->get();
        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function createClassroom()
    {
        return view('admin.classrooms.form', ['classroom' => null]);
    }

    public function storeClassroom(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:50',
            'location' => 'nullable|string|max:255',
        ]);

        Classroom::create($data);
        return redirect()->route('admin.classrooms')->with('success', "Salón «{$data['name']}» creado.");
    }

    public function editClassroom($id)
    {
        $classroom = Classroom::findOrFail($id);
        return view('admin.classrooms.form', compact('classroom'));
    }

    public function updateClassroom(Request $request, $id)
    {
        $classroom = Classroom::findOrFail($id);
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:50',
            'location' => 'nullable|string|max:255',
        ]);

        $classroom->update($data);
        return redirect()->route('admin.classrooms')->with('success', "Salón «{$classroom->name}» actualizado.");
    }

    public function deleteClassroom($id)
    {
        $classroom = Classroom::findOrFail($id);
        $classroom->delete();
        return redirect()->route('admin.classrooms')->with('success', "Salón «{$classroom->name}» eliminado.");
    }

    // ─── Schedules CRUD ──────────────────────────────────────

    public function schedules(Request $request)
    {
        $query = Schedule::with(['course', 'classroom', 'teacher']);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->get();
        $courses = Course::where('active', true)->orderBy('name')->get();
        $teachers = User::where('role', 'maestro')->orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'courses', 'teachers'));
    }

    public function createSchedule()
    {
        $courses = Course::where('active', true)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $teachers = User::where('role', 'maestro')->orderBy('name')->get();

        return view('admin.schedules.form', ['schedule' => null, 'courses' => $courses, 'classrooms' => $classrooms, 'teachers' => $teachers]);
    }

    public function storeSchedule(Request $request)
    {
        $data = $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'teacher_id'   => 'required|exists:users,id',
            'day_of_week'  => 'required|string',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
            'active'       => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        Schedule::create($data);

        return redirect()->route('admin.schedules')->with('success', 'Horario creado.');
    }

    public function editSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $courses = Course::where('active', true)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $teachers = User::where('role', 'maestro')->orderBy('name')->get();

        return view('admin.schedules.form', compact('schedule', 'courses', 'classrooms', 'teachers'));
    }

    public function updateSchedule(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $data = $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'teacher_id'   => 'required|exists:users,id',
            'day_of_week'  => 'required|string',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
            'active'       => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $schedule->update($data);

        return redirect()->route('admin.schedules')->with('success', 'Horario actualizado.');
    }

    public function deleteSchedule($id)
    {
        Schedule::findOrFail($id)->delete();
        return redirect()->route('admin.schedules')->with('success', 'Horario eliminado.');
    }

    // ─── Attendance Report ───────────────────────────────────

    public function attendanceReport(Request $request)
    {
        $query = Attendance::with(['user', 'schedule.course', 'schedule.classroom', 'lesson', 'classRegistration']);

        if ($request->filled('course_id')) {
            $query->whereHas('schedule', fn($q) => $q->where('course_id', $request->course_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('class_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('class_date', '<=', $request->date_to);
        }
        if ($request->filled('student_id')) {
            $query->where('user_id', $request->student_id);
        }

        $attendances = $query->orderByDesc('class_date')->orderByDesc('marked_at')->paginate(30)->withQueryString();

        $courses = Course::orderBy('name')->get();
        $students = User::where('role', 'alumno')->orderBy('name')->get();

        // Summary stats for filtered results
        $filteredQuery = Attendance::query();
        if ($request->filled('course_id')) {
            $filteredQuery->whereHas('schedule', fn($q) => $q->where('course_id', $request->course_id));
        }
        if ($request->filled('date_from')) {
            $filteredQuery->whereDate('class_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $filteredQuery->whereDate('class_date', '<=', $request->date_to);
        }
        if ($request->filled('student_id')) {
            $filteredQuery->where('user_id', $request->student_id);
        }

        $summary = [
            'total'   => $filteredQuery->count(),
            'present' => (clone $filteredQuery)->where('attended', true)->count(),
            'absent'  => (clone $filteredQuery)->where('attended', false)->count(),
            'qr'      => (clone $filteredQuery)->where('marked_via', 'qr')->count(),
            'manual'  => (clone $filteredQuery)->where('marked_via', 'manual')->count(),
        ];

        return view('admin.attendance.report', compact('attendances', 'courses', 'students', 'summary'));
    }

    // ─── Impersonate (switch to any user's view) ────────────

    public function impersonate($id)
    {
        $target = User::findOrFail($id);
        session()->put('admin_impersonator_id', Auth::id());
        Auth::login($target);

        return match ($target->role) {
            'maestro' => redirect()->route('maestro.dashboard'),
            'alumno'  => redirect()->route('alumno.mis-cursos'),
            default   => redirect()->route('admin.dashboard'),
        };
    }

    public function stopImpersonate()
    {
        $adminId = session()->pull('admin_impersonator_id');
        if ($adminId) {
            Auth::login(User::findOrFail($adminId));
        }
        return redirect()->route('admin.dashboard');
    }
}
