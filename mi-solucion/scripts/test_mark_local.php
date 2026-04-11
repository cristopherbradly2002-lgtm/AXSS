<?php
// Script de prueba: genera una URL firmada y llama internamente al endpoint markLocal
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Initialize kernel to handle requests
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// Bootstrap the application so service providers and config are available
$kernel->bootstrap();

// Generate a signed URL for attendance.mark
$fakeRequest = Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $fakeRequest);
$urlGenerator = $app->make('url');
// Adjust these IDs to match your DB if needed
$params = [
    'schedule' => 1,
    'lesson' => 1,
    'date' => date('Y-m-d'),
    'student' => 1,
];
$signedUrl = $urlGenerator->temporarySignedRoute('attendance.mark', now()->addMinutes(60), $params);

echo "Signed URL: $signedUrl\n";

// Create a POST request to /attendance/mark-local with JSON body
$body = json_encode(['full_url' => $signedUrl, 'timezone' => 'America/Mexico_City']);
$request = Illuminate\Http\Request::create('/attendance/mark-local', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);

// Quick self-check: validate signature using UrlGenerator
$fakeGet = Illuminate\Http\Request::create($signedUrl, 'GET');
$valid = $urlGenerator->hasValidSignature($fakeGet, false);
echo "Self-check signature valid? " . ($valid ? 'yes' : 'no') . "\n";
// Also test default (absolute) validation
$validAbs = $urlGenerator->hasValidSignature($fakeGet, true);
echo "Self-check signature valid (absolute)? " . ($validAbs ? 'yes' : 'no') . "\n";

// Call controller method directly to avoid CSRF middleware when testing
$controller = new App\Http\Controllers\AttendanceController();
$resp = $controller->markLocal($request);

if ($resp instanceof Illuminate\Http\JsonResponse) {
    echo "HTTP Status: " . $resp->getStatusCode() . "\n";
    echo $resp->getContent() . "\n";
} elseif ($resp instanceof Illuminate\Http\RedirectResponse) {
    echo "Redirect to: " . $resp->getTargetUrl() . "\n";
} else {
    // Fallback: cast to string
    echo (string) $resp . "\n";
}
