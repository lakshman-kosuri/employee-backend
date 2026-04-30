<?php
require_once "../config/cors.php";
include "../config/database.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$id          = $data['id'] ?? null;
$title       = $data['title'] ?? null;
$description = $data['description'] ?? null;
$date        = $data['date'] ?? null;
$start_time  = $data['start_time'] ?? null;
$end_time    = $data['end_time'] ?? null;
$meeting_link = $data['meeting_link'] ?? null;

if (!$id) {
    echo json_encode(["status" => false, "message" => "Meeting id required"]);
    exit();
}

// Build the query dynamically — only update fields that are sent
$fields = [];
$types  = "";
$values = [];

if ($title !== null)       { $fields[] = "title = ?";        $types .= "s"; $values[] = $title; }
if ($description !== null) { $fields[] = "description = ?";  $types .= "s"; $values[] = $description; }
if ($date !== null)        { $fields[] = "date = ?";         $types .= "s"; $values[] = $date; }
if ($start_time !== null)  { $fields[] = "start_time = ?";   $types .= "s"; $values[] = $start_time; }
if ($end_time !== null)    { $fields[] = "end_time = ?";     $types .= "s"; $values[] = $end_time; }
if ($meeting_link !== null){ $fields[] = "meeting_link = ?"; $types .= "s"; $values[] = $meeting_link; }

// When rescheduling, reset status back to scheduled
if ($date !== null || $start_time !== null || $end_time !== null) {
    $fields[] = "status = 'scheduled'";
    $fields[] = "completed_at = NULL";
}

if (empty($fields)) {
    echo json_encode(["status" => false, "message" => "No fields to update"]);
    exit();
}

$types  .= "i";
$values[] = $id;

$sql  = "UPDATE meetings SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$values);
$stmt->execute();

echo json_encode(["status" => true, "message" => "Meeting updated successfully"]);
?>