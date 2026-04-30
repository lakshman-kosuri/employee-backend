<?php
include("../config/database.php");
require_once "../config/cors.php";

// Decode the incoming JSON data
$data = json_decode(file_get_contents("php://input"));

// Log the raw data for debugging
error_log("Received data: " . file_get_contents("php://input"));

// Check if 'emp_id' is passed in the request data
if (!isset($data->emp_id)) {
    // Log and return an error if emp_id is not set
    error_log("emp_id is missing");
    echo json_encode(["status" => false, "message" => "emp_id is required"]);
    exit;
}

$emp_id = $data->emp_id;  // Get the emp_id of the employee to be deleted

// Log the emp_id for debugging purposes
error_log("Received emp_id for deletion: " . $emp_id);

// Step 1: Check if the employee exists by emp_id
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE emp_id=?");
$checkStmt->bind_param("s", $emp_id);  // Bind emp_id as a string (since it's a VARCHAR)
$checkStmt->execute();
$checkStmt->bind_result($count);

// Fetch the result
$checkStmt->fetch();

// Log the result of the employee check
error_log("Employee exists check result: " . $count);

// Close the check statement after use
$checkStmt->close();

// If employee doesn't exist, exit with a message
if ($count == 0) {
    echo json_encode(["status" => false, "message" => "Employee not found"]);
    exit;
}

// Step 2: Proceed with the deletion based on emp_id
$stmt = $conn->prepare("DELETE FROM employees WHERE emp_id=?");
$stmt->bind_param("s", $emp_id);  // Bind emp_id as a string

// Log the SQL query that will be executed
error_log("Executing DELETE query: DELETE FROM employees WHERE emp_id = " . $emp_id);

// Execute the delete query
if ($stmt->execute()) {
    // Send a success response after deletion
    echo json_encode(["status" => true, "message" => "Employee deleted"]);
} else {
    // If the deletion fails, send a failure response
    echo json_encode(["status" => false, "message" => "Delete failed", "error" => $stmt->error]);
}

// Close the delete statement after use
$stmt->close();
?>