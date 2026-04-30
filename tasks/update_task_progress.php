<?php
require_once "../config/cors.php";
include("../config/database.php");

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? '';
$progress_updates = $data['progress_updates'] ?? '';

if (!$id) {
    echo json_encode(["error" => "Task id is required"]);
    exit();
}

$stmt = $conn->prepare("UPDATE tasks SET progress_updates=? WHERE id=?");
$stmt->bind_param("si", $progress_updates, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Progress updated"]);
} else {
    echo json_encode(["status" => false, "error" => $stmt->error]);
}

$stmt->close();
?>