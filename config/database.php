cat > config/database.php << 'EOF'
<?php
$host     = getenv('MYSQLHOST')     ?: 'localhost';
$port     = getenv('MYSQLPORT')     ?: 3306;
$username = getenv('MYSQLUSER')     ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'railway';

$conn = mysqli_connect($host, $username, $password, $database, (int)$port);

if (!$conn) {
    die(json_encode([
        "status" => false,
        "message" => "DB Connection failed: " . mysqli_connect_error()
    ]));
}
?>
EOF