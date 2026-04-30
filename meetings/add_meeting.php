<?php
require_once "../config/cors.php";
include "../config/database.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$title       = $data['title'] ?? '';
$description = $data['description'] ?? '';
$date        = $data['date'] ?? '';
$start_time  = $data['start_time'] ?? '';
$end_time    = $data['end_time'] ?? '';
$emp_ids     = $data['emp_ids'] ?? [];

if (!$title || !$date || !$start_time || !$end_time || empty($emp_ids)) {
    echo json_encode(["status" => false, "message" => "Missing required fields"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO meetings (emp_id, title, description, date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($emp_ids as $emp_id) {
    $stmt->bind_param("ssssss", $emp_id, $title, $description, $date, $start_time, $end_time);
    $stmt->execute();
}

echo json_encode(["status" => true, "message" => "Meeting scheduled"]);
?>