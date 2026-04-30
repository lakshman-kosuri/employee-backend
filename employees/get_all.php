<?php
require_once "../config/cors.php";
include("../config/database.php");

// Use a simple query to test
$sql = "SELECT * FROM employees ORDER BY id DESC";
$result = $conn->query($sql);

$employees = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Set header to JSON so Axios knows how to parse it
header('Content-Type: application/json');
echo json_encode($employees);
?>