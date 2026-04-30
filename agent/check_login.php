<?php
header("Content-Type: application/json");

include("../config/database.php");

$emp_id = $_GET['emp_id'] ?? null;

if (!$emp_id) {
    echo json_encode(["loggedIn" => false]);
    exit;
}

// ✅ Use prepared statement (SAFE)
$stmt = $conn->prepare("
    SELECT id FROM attendance 
    WHERE emp_id = ? 
    AND date = CURDATE()
    AND login_time IS NOT NULL 
    AND logout_time IS NULL
");

$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo json_encode(["loggedIn" => true]);
} else {
    echo json_encode(["loggedIn" => false]);
}
?>