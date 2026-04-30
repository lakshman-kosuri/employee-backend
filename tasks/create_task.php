<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/database.php");

// Try JSON first
$data = json_decode(file_get_contents("php://input"), true);

// If JSON empty, use POST
if(!$data){
    $data = $_POST;
}

$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$priority = $data['priority'] ?? '';
$deadline = $data['deadline'] ?? '';
$status = $data['status'] ?? 'Pending';
$empId = $data['employeeId'] ?? ''; // changed variable name for clarity

// Updated column name
$sql = "INSERT INTO tasks(title, description, priority, due_date, status, emp_id)
        VALUES('$title', '$description', '$priority', '$deadline', '$status', '$empId')";

if($conn->query($sql)){
    echo json_encode(["message"=>"Task created"]);
}else{
    echo json_encode(["error"=>$conn->error]);
}
