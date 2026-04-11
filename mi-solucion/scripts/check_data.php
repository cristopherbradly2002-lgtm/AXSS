<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$attendances = \App\Models\Attendance::all();
foreach ($attendances as $a) {
    echo "ID={$a->id} reg={$a->class_registration_id} user={$a->user_id} lesson={$a->lesson_id} sched={$a->schedule_id} date={$a->class_date->format('Y-m-d')} via={$a->marked_via} at={$a->marked_at} tz={$a->marked_at_tz}\n";
}

echo "\n--- Course User Pivot ---\n";
$pivots = \Illuminate\Support\Facades\DB::table('course_user')->get();
foreach ($pivots as $p) {
    echo "user={$p->user_id} course={$p->course_id} current_lesson={$p->current_lesson}\n";
}

echo "\n--- Class Registrations for schedule 1 ---\n";
$regs = \App\Models\ClassRegistration::where('schedule_id', 1)->get();
foreach ($regs as $r) {
    echo "ID={$r->id} user={$r->user_id} sched={$r->schedule_id} date={$r->class_date->format('Y-m-d')} status={$r->status}\n";
}
