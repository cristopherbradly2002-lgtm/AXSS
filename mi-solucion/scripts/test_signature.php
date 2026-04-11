<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

// Generate a signed URL exactly like showQr does
$signedUrl = URL::temporarySignedRoute(
    'attendance.mark',
    now()->addMinutes(60),
    ['schedule' => 1, 'lesson' => 1, 'date' => '2026-04-09']
);
echo "Generated URL: $signedUrl\n\n";

// Validate with absolute=true (the fix)
$fake = Request::create($signedUrl, 'GET');
$validTrue = URL::hasValidSignature($fake, true);
echo "hasValidSignature(absolute=true):  " . ($validTrue ? 'PASS ✓' : 'FAIL ✗') . "\n";

// Validate with absolute=false (the old broken way)
$validFalse = URL::hasValidSignature($fake, false);
echo "hasValidSignature(absolute=false): " . ($validFalse ? 'PASS ✓' : 'FAIL ✗') . "\n";

// Also test with student parameter (per-student QR)
$signedUrlStudent = URL::temporarySignedRoute(
    'attendance.mark',
    now()->addMinutes(60),
    ['schedule' => 1, 'lesson' => 1, 'date' => '2026-04-09', 'student' => 5]
);
echo "\nPer-student URL: $signedUrlStudent\n";
$fake2 = Request::create($signedUrlStudent, 'GET');
$valid2 = URL::hasValidSignature($fake2, true);
echo "hasValidSignature(absolute=true):  " . ($valid2 ? 'PASS ✓' : 'FAIL ✗') . "\n";
