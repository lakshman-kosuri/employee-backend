<?php
include("../config/database.php");
require_once "../config/cors.php";

// Try JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
$id = $data['id'] ?? '';
$status = $data['status'] ?? '';

if (!$id || !$status) {
    echo json_encode(["error" => "id and status are required"]);
    exit();
}

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("UPDATE tasks SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Task updated"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
?>
