<?php
require_once "../config/cors.php";
include "../config/database.php";
header("Content-Type: application/json");

$emp_id = $_GET['emp_id'] ?? null;
if (!$emp_id) {
    echo json_encode(["error" => "emp_id required"]);
    exit();
}

$stmt = $conn->prepare(
    "SELECT * FROM meetings 
     WHERE emp_id = ? AND status != 'cancelled'
     ORDER BY date ASC, start_time ASC"
);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
echo json_encode($data);
?>