<?php
require_once "../config/cors.php";
include("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->id)) {
    echo json_encode(["status" => false, "message" => "Missing Employee ID"]);
    exit;
}

// Map variables
$id = $data->id; // The Primary Key
$emp_id = $data->emp_id;
$name = $data->name;
$email = $data->email;
$password = $data->password; // Saving as plain text as requested
$role = $data->role;
$dept = $data->dept;

// SQL UPDATE query
$sql = "UPDATE employees SET 
        emp_id = ?, 
        name = ?, 
        email = ?, 
        password = ?, 
        role = ?, 
        dept = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
// "ssssssi" means 6 strings and 1 integer (for the id)
$stmt->bind_param("ssssssi", $emp_id, $name, $email, $password, $role, $dept, $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => true, "message" => "Employee updated successfully"]);
    } else {
        echo json_encode(["status" => true, "message" => "No changes made, but query successful"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Update failed: " . $stmt->error]);
}
?>