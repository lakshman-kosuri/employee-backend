<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/cors.php";
require_once "../config/database.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Login API working"
    ]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode([
        "status" => false,
        "message" => "No input"
    ]);
    exit();
}

if (!isset($data->email) || !isset($data->password)) {
    echo json_encode([
        "status" => false,
        "message" => "Email and Password required"
    ]);
    exit();
}

$email = trim($data->email);
$password = trim($data->password);


/* ADMIN */
$stmt = $conn->prepare(
    "SELECT id,name,email,password,role FROM users WHERE email=?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user["password"])) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid password"
        ]);
        exit();
    }

} else {

    /* EMPLOYEE */
    $stmt = $conn->prepare(
    "SELECT id, emp_id, name, email, password FROM employees WHERE email=?"
);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            "status" => false,
            "message" => "User not found"
        ]);
        exit();
    }

    $user = $result->fetch_assoc();

    if ($password !== $user["password"]) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid password"
        ]);
        exit();
    }

    $user["role"] = "employee";
}

unset($user["password"]);

echo json_encode([
    "status" => true,
    "user" => $user
]);