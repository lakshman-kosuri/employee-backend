<?php
include("../config/database.php");
require_once "../config/cors.php";

$emp_id = $_GET['emp_id'] ?? '';

if (!$emp_id) {
    echo json_encode(["error" => "emp_id is required"]);
    exit();
}

$stmt = $conn->prepare("SELECT t.*, e.name 
        FROM tasks t
        JOIN employees e ON t.emp_id = e.emp_id
        WHERE t.emp_id = ?
        ORDER BY t.created_at DESC");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
?>