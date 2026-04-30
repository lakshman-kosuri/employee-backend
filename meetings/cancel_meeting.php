<?php
require_once "../config/cors.php";
include "../config/database.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$id   = $data['id'] ?? null;

// Support cancelling all rows for a meeting group (same title+date+time)
// OR just a single row by id
$scope = $data['scope'] ?? 'single'; // 'single' or 'all'

if (!$id) {
    echo json_encode(["status" => false, "message" => "Meeting id required"]);
    exit();
}

if ($scope === 'all') {
    // Get the title/date/times of this meeting, then cancel all matching rows
    $ref = $conn->prepare("SELECT title, date, start_time, end_time FROM meetings WHERE id = ?");
    $ref->bind_param("i", $id);
    $ref->execute();
    $row = $ref->get_result()->fetch_assoc();

    if (!$row) {
        echo json_encode(["status" => false, "message" => "Meeting not found"]);
        exit();
    }

    $stmt = $conn->prepare(
        "UPDATE meetings SET status = 'cancelled' 
         WHERE title = ? AND date = ? AND start_time = ? AND end_time = ?"
    );
    $stmt->bind_param("ssss", $row['title'], $row['date'], $row['start_time'], $row['end_time']);
} else {
    // Cancel only this specific row (one employee's instance)
    $stmt = $conn->prepare("UPDATE meetings SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $id);
}

$stmt->execute();
echo json_encode(["status" => true, "message" => "Meeting cancelled"]);
?>