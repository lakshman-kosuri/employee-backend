<?php
require_once "../config/cors.php";
include("../config/database.php");

// Get today's date
$today = date('Y-m-d');

$sql = "SELECT 
    e.emp_id,
    e.name,
    e.role,
    e.dept,

    -- ✅ REAL TIME (seconds from DB)
    COALESCE(SUM(a.active), 0) as activeTime,
    COALESCE(SUM(a.idle), 0) as idleTime,

    -- Optional tracking info
    MIN(a.created_at) as login_time,
    MAX(a.created_at) as last_activity

FROM employees e

LEFT JOIN employee_activity a 
    ON e.emp_id = a.emp_id 
    AND DATE(a.created_at) = '$today'

GROUP BY e.emp_id
ORDER BY e.name ASC";

$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {

    $active = (int)$row['activeTime']; // seconds
    $idle = (int)$row['idleTime'];     // seconds

    $total = $active + $idle;

    // ✅ Efficiency Score
    $score = 0;
    if ($total > 0) {
        $score = round(($active / $total) * 100);
    }

    $data[] = [
        "emp_id" => $row['emp_id'],
        "name" => $row['name'],
        "role" => $row['role'],
        "dept" => $row['dept'],

        // ✅ Send RAW seconds (frontend will format)
        "activeTime" => $active,
        "idleTime" => $idle,
        "score" => $score,

        // Optional
        "login" => $row['login_time'],
        "logout" => $row['last_activity']
    ];
}

echo json_encode([
    "status" => true,
    "data" => $data
]);
?>