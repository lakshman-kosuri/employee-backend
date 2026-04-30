<?php
// Allow frontend requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// Include DB
include("../config/database.php");

// Get date
$date = $_GET['date'] ?? '';
if (!$date) die("Date missing");

// Set CSV headers
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=task_report_$date.csv");

$output = fopen('php://output', 'w');

// CSV header row
fputcsv($output, ['Employee', 'Task Title', 'Priority', 'Deadline', 'Status']);

// Determine if monthly or specific date
$query = (strlen($date) === 7)
    ? "SELECT e.name, t.title, t.priority, t.due_date, t.status
       FROM tasks t
       JOIN employees e ON t.emp_id = e.emp_id
       WHERE DATE_FORMAT(t.due_date,'%Y-%m') = ?"
    : "SELECT e.name, t.title, t.priority, t.due_date, t.status
       FROM tasks t
       JOIN employees e ON t.emp_id = e.emp_id
       WHERE t.due_date = ?";

$stmt = $conn->prepare($query);
if (!$stmt) die("Prepare failed: " . $conn->error);

$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

// Write each row to CSV
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);

// Optional: save report history
$stmt2 = $conn->prepare("INSERT INTO reports_history (file_name, category, file_size) VALUES (?, ?, ?)");
$fileName = "task_report_$date.csv";
$category = "Tasks";
$fileSize = null; // optional
$stmt2->bind_param("sss", $fileName, $category, $fileSize);
$stmt2->execute();

exit;