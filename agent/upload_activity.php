<?php
require_once "../config/cors.php";
include("../config/database.php");

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["status" => false, "message" => "No data"]);
    exit;
}

$emp_id = $data->emp_id;
$screenshot = $data->screenshot;
$status = $data->status;


// ✅ FORCE CORRECT VALUES (per interval)
$interval = 60; // seconds (adjust if your tracker uses 10s, 5s etc.)

if ($status === "active" || $status === "meeting") {
    $active = $interval;
    $idle = 0;
} else {
    $active = 0;
    $idle = $interval;
}

// ✅ Calculate score properly
$total = $active + $idle;
$score = ($total > 0) ? round(($active / $total) * 100) : 0;

// default path
$filePath = null;

// 📸 SAVE IMAGE ONLY IF EXISTS
if ($screenshot) {

    // create filename
    $fileName = time() . "_" . $emp_id . ".jpg";
    $filePath = "screenshots/" . $fileName;

    // decode base64
    $imageData = base64_decode($screenshot);

    // save file
    file_put_contents($filePath, $imageData);
}

// insert
$stmt = $conn->prepare("
    INSERT INTO employee_activity (emp_id, screenshot, status, active, idle, score) 
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("sssiii", $emp_id, $filePath, $status, $active, $idle, $score);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Saved"]);
} else {
    echo json_encode(["status" => false, "message" => $stmt->error]);
}
?>