<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

include("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

$emp_id = $data->emp_id ?? null;
$reason = $data->reason ?? "";
$from = $data->fromDate ?? null;
$to = $data->toDate ?? null;

if (!$emp_id || !$from || !$to) {
    echo json_encode([
        "status" => false,
        "message" => "Required fields missing"
    ]);
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO leaves (emp_id, reason, from_date, to_date)
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("ssss", $emp_id, $reason, $from, $to);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Leave applied successfully"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Failed to apply leave"
    ]);
}