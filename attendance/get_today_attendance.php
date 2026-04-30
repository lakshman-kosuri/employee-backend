<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/database.php");

if (!isset($_GET['emp_id'])) {
    echo json_encode([
        "status"  => false,
        "message" => "Employee ID is required"
    ]);
    exit();
}

$emp_id = $_GET['emp_id'];
$today  = date("Y-m-d");

$stmt = $conn->prepare("SELECT * FROM attendance WHERE emp_id = ? AND date = ?");
$stmt->bind_param("ss", $emp_id, $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $attendance = $result->fetch_assoc();

    // ✅ Parse sessions JSON string back into array for the frontend
    $sessions = [];
    if (!empty($attendance['sessions'])) {
        $decoded = json_decode($attendance['sessions'], true);
        if (is_array($decoded)) {
            $sessions = $decoded;
        }
    }

    echo json_encode([
        "status" => true,
        "data"   => [
            "sessions"           => $sessions,               // ✅ always an array
            "loginTime"          => $attendance['login_time'],
            "logoutTime"         => $attendance['logout_time'],
            "loginLocationName"  => $attendance['login_location_name'],
            "logoutLocationName" => $attendance['logout_location_name'],
            "date"               => $attendance['date'],
            "status"             => $attendance['status'],
            "workHours"          => $attendance['work_hours'],
        ]
    ]);
} else {
    echo json_encode([
        "status" => true,
        "data"   => [
            "sessions"           => [],
            "loginTime"          => null,
            "logoutTime"         => null,
            "loginLocationName"  => null,
            "logoutLocationName" => null,
            "date"               => $today,
            "status"             => "NOT_MARKED",
            "workHours"          => null,
        ]
    ]);
}

$conn->close();
?>