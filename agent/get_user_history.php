<?php
require_once "../config/cors.php";
include("../config/database.php");

$emp_id = $_GET['emp_id'] ?? null;
$view = $_GET['view'] ?? 'daily';

if (!$emp_id) {
    echo json_encode(["status"=>false, "message"=>"emp_id required"]);
    exit;
}

// ---------------- DAILY ----------------
if ($view === "daily") {

    $sql = "SELECT 
                DATE(created_at) as day,
                SUM(CASE WHEN status='active' THEN 60 ELSE 0 END) as active,
                SUM(CASE WHEN status='idle' THEN 60 ELSE 0 END) as idle
            FROM employee_activity
            WHERE emp_id = '$emp_id'
            GROUP BY DATE(created_at)
            ORDER BY day ASC";

}

// ---------------- WEEKLY ----------------
elseif ($view === "weekly") {

    $sql = "SELECT 
                YEARWEEK(created_at) as week,
                SUM(CASE WHEN status='active' THEN 60 ELSE 0 END) as active,
                SUM(CASE WHEN status='idle' THEN 60 ELSE 0 END) as idle
            FROM employee_activity
            WHERE emp_id = '$emp_id'
            GROUP BY YEARWEEK(created_at)
            ORDER BY week ASC";
}

// ---------------- MONTHLY ----------------
else {

    $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(CASE WHEN status='active' THEN 60 ELSE 0 END) as active,
                SUM(CASE WHEN status='idle' THEN 60 ELSE 0 END) as idle
            FROM employee_activity
            WHERE emp_id = '$emp_id'
            GROUP BY month
            ORDER BY month ASC";
}

$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {

    $active = (int)$row['active'];
    $idle = (int)$row['idle'];

    $total = $active + $idle;

    $score = 0;
    if ($total > 0) {
        $score = round(($active / $total) * 100);
    }

    $data[] = [
        "active" => $active,
        "idle" => $idle,
        "score" => $score
    ];
}

echo json_encode([
    "status" => true,
    "data" => $data
]);
?>