<?php
include("../config/database.php");
require_once "../config/cors.php";

// Get employee ID from query string
$emp_id = $_GET['emp_id'] ?? '';

if (!$emp_id) {
    echo json_encode(["error" => "emp_id is required"]);
    exit();
}

// Use correct column names from your schema
$sql = "SELECT t.*, e.name 
        FROM tasks t
        JOIN employees e ON t.emp_id = e.emp_id
        WHERE t.emp_id = '$emp_id'
        ORDER BY t.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => $conn->error]);
    exit();
}

$tasks = [];
while($row = $result->fetch_assoc()){
    $tasks[] = $row;
}

echo json_encode($tasks);
?>
