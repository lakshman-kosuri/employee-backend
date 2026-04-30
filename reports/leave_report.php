<?php
// Allow all origins for frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // handle preflight
}

include("../config/database.php");

$date = $_GET['date'] ?? '';
if (!$date) die("Date missing");

// Set CSV headers
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=leave_report_$date.csv");

$output = fopen('php://output', 'w');
// CSV header
fputcsv($output, ['Employee','Leave Reason','From Date','To Date','Status']);

// Determine if monthly or specific date
$query = (strlen($date) === 7) 
    ? "SELECT e.name, l.reason, l.from_date, l.to_date, l.status
       FROM leaves l
       JOIN employees e ON l.emp_id = e.emp_id
       WHERE DATE_FORMAT(l.from_date,'%Y-%m') = ?"
    : "SELECT e.name, l.reason, l.from_date, l.to_date, l.status
       FROM leaves l
       JOIN employees e ON l.emp_id = e.emp_id
       WHERE l.from_date = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

// Write rows to CSV
while($row = $result->fetch_assoc()){
    fputcsv($output, $row);
}
fclose($output);

// Save report history (optional)
$stmt2 = $conn->prepare("INSERT INTO reports_history (file_name, category, file_size) VALUES (?, ?, ?)");
$category = "Leave";
$fileSize = null; // optional
$fileName = "leave_report_$date.csv";
$stmt2->bind_param("sss", $fileName, $category, $fileSize);
$stmt2->execute();

exit;