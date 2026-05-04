<?php

echo json_encode([
    "host" => getenv('MYSQLHOST'),
    "user" => getenv('MYSQLUSER'),
    "db"   => getenv('MYSQLDATABASE'),
    "port" => getenv('MYSQLPORT')
]);
exit;

$host     = getenv('MYSQLHOST');
$port     = getenv('MYSQLPORT');
$username = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$database = getenv('MYSQLDATABASE');

if (!$host || !$username || !$database) {
    die(json_encode([
        "status" => false,
        "message" => "Missing Railway DB environment variables"
    ]));
}

$conn = mysqli_connect($host, $username, $password, $database, (int)$port);

if (!$conn) {
    die(json_encode([
        "status" => false,
        "message" => "DB Connection failed: " . mysqli_connect_error()
    ]));
}