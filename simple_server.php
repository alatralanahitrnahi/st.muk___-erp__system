<?php
// Simple PHP server for testing frontend
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Serve static files
if ($path === '/' || $path === '/login.html') {
    readfile(__DIR__ . '/public/login.html');
} elseif ($path === '/admin.html') {
    readfile(__DIR__ . '/public/admin.html');
} elseif ($path === '/student.html') {
    readfile(__DIR__ . '/public/student.html');
} elseif ($path === '/faculty.html') {
    readfile(__DIR__ . '/public/faculty.html');
} elseif ($path === '/performance.html') {
    readfile(__DIR__ . '/public/performance.html');
} else {
    http_response_code(404);
    echo "Page not found";
}
?>