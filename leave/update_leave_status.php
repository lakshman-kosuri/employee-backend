<?php
// ✅ CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Show errors (for debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ JSON response
header("Content-Type: application/json");

// Include database connection
include("../config/database.php");

// Get POSTed JSON data
$data = json_decode(file_get_contents("php://input"));

// ✅ FIX: safely read values
$id = isset($data->leave_id) ? (int)$data->leave_id : null;
$status = $data->status ?? null;

// Validate input
if (!$id || !$status) {
    echo json_encode(["status" => false, "message" => "Leave ID or status missing"]);
    exit();
}

// ✅ FIX: use correct column name `id`
$stmt = $conn->prepare("UPDATE leaves SET status=? WHERE id=?");

// Check prepare
if (!$stmt) {
    echo json_encode(["status" => false, "message" => $conn->error]);
    exit();
}

// Bind params
$stmt->bind_param("si", $status, $id);

// Execute
if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Leave status updated"]);
} else {
    echo json_encode(["status" => false, "message" => $stmt->error]);
}
?>