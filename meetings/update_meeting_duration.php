<?php
require_once "../config/cors.php";
include "../config/database.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$id              = $data['id'] ?? null;
$actual_duration = $data['actual_duration'] ?? null;

if (!$id) {
    echo json_encode(["status" => false, "message" => "Meeting id required"]);
    exit();
}

$stmt = $conn->prepare("UPDATE meetings SET actual_duration = ? WHERE id = ?");
$stmt->bind_param("ii", $actual_duration, $id);
$stmt->execute();
echo json_encode(["status" => true, "message" => "Duration updated"]);
?>