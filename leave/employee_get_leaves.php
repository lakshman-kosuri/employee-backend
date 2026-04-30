<?php
// ✅ CORS HEADERS (VERY IMPORTANT)
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/database.php");

$emp_id = $_GET['emp_id'] ?? null;

if (!$emp_id) {
    echo json_encode([
        "status" => false,
        "message" => "emp_id missing"
    ]);
    exit();
}

$sql = "SELECT * FROM leaves 
        WHERE emp_id = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emp_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);