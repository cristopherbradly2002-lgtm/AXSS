<?php
/**
 * One-time script: remove duplicate attendance records.
 * Keeps the one with the latest updated_at (or highest id if tied).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->handleRequest(Illuminate\Http\Request::capture());

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

echo "=== Before cleanup ===\n";
$all = Attendance::orderBy('id')->get();
foreach ($all as $a) {
    echo "ID={$a->id} reg={$a->class_registration_id} user={$a->user_id} sched={$a->schedule_id} date={$a->class_date} lesson={$a->lesson_id} at={$a->marked_at}\n";
}

// Group by the unique key
$grouped = $all->groupBy(function ($a) {
    return $a->class_registration_id . '-' . $a->user_id . '-' . $a->schedule_id . '-' . substr($a->class_date, 0, 10);
});

$deleted = 0;
foreach ($grouped as $key => $records) {
    if ($records->count() <= 1) continue;

    // Keep the one with the latest marked_at, fallback to highest id
    $keep = $records->sortByDesc(function ($r) {
        return ($r->marked_at ?? '0000') . '-' . str_pad($r->id, 10, '0', STR_PAD_LEFT);
    })->first();

    $toDelete = $records->where('id', '!=', $keep->id);
    foreach ($toDelete as $dup) {
        echo "DELETING duplicate ID={$dup->id} (keeping ID={$keep->id})\n";
        $dup->delete();
        $deleted++;
    }
}

echo "\nDeleted {$deleted} duplicate(s).\n";

echo "\n=== After cleanup ===\n";
$all = Attendance::orderBy('id')->get();
foreach ($all as $a) {
    echo "ID={$a->id} reg={$a->class_registration_id} user={$a->user_id} sched={$a->schedule_id} date={$a->class_date} lesson={$a->lesson_id} at={$a->marked_at}\n";
}
