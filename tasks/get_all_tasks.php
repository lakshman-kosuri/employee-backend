<?php
include("../config/database.php");
require_once "../config/cors.php";

header("Content-Type: application/json");

// Fetch ALL tasks joined with employee name — for admin view
$sql = "SELECT t.*, e.name 
        FROM tasks t
        JOIN employees e ON t.emp_id = e.emp_id
        ORDER BY t.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => $conn->error]);
    exit();
}

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
?>