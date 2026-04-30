<?php
require_once "../config/cors.php";
include "../config/database.php";
header("Content-Type: application/json");

// Return all meetings grouped — each unique title+date+start+end is one "meeting event"
// with a list of employees assigned
$sql = "
    SELECT 
        MIN(m.id) as id,
        m.title,
        m.description,
        m.date,
        m.start_time,
        m.end_time,
        m.meeting_link,
        m.status,
        m.completed_at,
        COUNT(m.emp_id) as assigned_count,
        GROUP_CONCAT(e.name ORDER BY e.name SEPARATOR ', ') as employee_names,
        GROUP_CONCAT(m.emp_id ORDER BY e.name SEPARATOR ',') as emp_ids,
        GROUP_CONCAT(m.id ORDER BY e.name SEPARATOR ',') as row_ids
    FROM meetings m
    LEFT JOIN employees e ON m.emp_id = e.emp_id
    GROUP BY m.title, m.description, m.date, m.start_time, m.end_time, m.meeting_link, m.status, m.completed_at
    ORDER BY m.date ASC, m.start_time ASC
";

$result = $conn->query($sql);
$data = [];
while ($row = $result->fetch_assoc()) {
    $row['emp_ids'] = explode(',', $row['emp_ids'] ?? '');
    $row['row_ids'] = array_map('intval', explode(',', $row['row_ids'] ?? ''));
    $data[] = $row;
}
echo json_encode($data);
?>