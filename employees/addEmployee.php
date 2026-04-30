<?php
require_once "../config/cors.php";
include("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["status" => false, "message" => "No data"]);
    exit;
}

$emp_id = $data->emp_id;
$name = $data->name;
$email = $data->email;

// CHANGE THIS: Remove password_hash
$password = $data->password; 

$role = $data->role;
$dept = $data->dept;

$stmt = $conn->prepare("INSERT INTO employees (emp_id, name, email, password, role, dept) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $emp_id, $name, $email, $password, $role, $dept);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Employee added"]);
} else {
    echo json_encode(["status" => false, "message" => $stmt->error]);
}
?>