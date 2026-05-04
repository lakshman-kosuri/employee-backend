<?php
function env(string $key): string {
    return getenv($key) 
        ?: ($_SERVER[$key] ?? '') 
        ?: ($_ENV[$key] ?? '');
}

$host = env('DB_HOST');
$port = env('DB_PORT') ?: '3306';
$name = env('DB_DATABASE');
$user = env('DB_USERNAME');
$pass = env('DB_PASSWORD');

$conn = mysqli_connect($host, $user, $pass, $name, (int)$port);

if (!$conn) {
    die(json_encode([
        "status" => false,
        "message" => "DB Connection failed: " . mysqli_connect_error()
    ]));
}
?>