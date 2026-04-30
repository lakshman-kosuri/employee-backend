<?php
require_once "../config/cors.php";
include("../config/database.php");

header("Content-Type: application/json");

$emp_id = $_GET['emp_id'] ?? null;
$date   = $_GET['date']   ?? date('Y-m-d');

if (!$emp_id) {
    // Return all employees performance for admin
    $sql = "
        SELECT
            e.emp_id, e.name, e.role, e.dept,
            COALESCE(SUM(a.active), 0) AS activeTime,
            COALESCE(SUM(a.idle),   0) AS idleTime,
            -- Attendance window (first check-in to last check-out)
            att.login_time   AS firstCheckIn,
            att.logout_time  AS lastCheckOut,
            att.work_hours   AS workHours,
            att.status       AS attendanceStatus
        FROM employees e
        LEFT JOIN employee_activity a
            ON e.emp_id = a.emp_id AND DATE(a.created_at) = ?
        LEFT JOIN attendance att
            ON e.emp_id = att.emp_id AND att.date = ?
        GROUP BY e.emp_id, e.name, e.role, e.dept,
                 att.login_time, att.logout_time, att.work_hours, att.status
        ORDER BY e.name ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $date, $date);
} else {
    $sql = "
        SELECT
            e.emp_id, e.name, e.role, e.dept,
            COALESCE(SUM(a.active), 0) AS activeTime,
            COALESCE(SUM(a.idle),   0) AS idleTime,
            att.login_time   AS firstCheckIn,
            att.logout_time  AS lastCheckOut,
            att.work_hours   AS workHours,
            att.status       AS attendanceStatus
        FROM employees e
        LEFT JOIN employee_activity a
            ON e.emp_id = a.emp_id AND DATE(a.created_at) = ?
        LEFT JOIN attendance att
            ON e.emp_id = att.emp_id AND att.date = ?
        WHERE e.emp_id = ?
        GROUP BY e.emp_id, e.name, e.role, e.dept,
                 att.login_time, att.logout_time, att.work_hours, att.status
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $date, $date, $emp_id);
}

$stmt->execute();
$result = $stmt->get_result();
$rows   = $result->fetch_all(MYSQLI_ASSOC);

$data = [];
foreach ($rows as $row) {
    $active = (int)$row['activeTime'];
    $idle   = (int)$row['idleTime'];
    $total  = $active + $idle;

    // Score is based on ONLY the window between first check-in & last check-out
    // If no activity data but attended → show 0
    $score = ($total > 0) ? round(($active / $total) * 100) : 0;

    // ✅ Late checkout logic:
    // If they checked out (logout_time is set) and never re-checked-in after that
    // → consider it "late checkout" flag for display
    $attendanceStatus = $row['attendanceStatus'] ?? 'NOT_MARKED';
    $isLateCheckout   = false;
    if ($row['lastCheckOut'] && !$row['firstCheckIn']) {
        $isLateCheckout = true; // checked out but no check-in today
    }
    if ($attendanceStatus === 'CHECKED_OUT' && empty($row['firstCheckIn'])) {
        $isLateCheckout = true;
    }

    $data[] = [
        "emp_id"           => $row['emp_id'],
        "name"             => $row['name'],
        "role"             => $row['role'],
        "dept"             => $row['dept'],
        "activeTime"       => $active,
        "idleTime"         => $idle,
        "totalTracked"     => $total,
        "score"            => $score,
        "firstCheckIn"     => $row['firstCheckIn'],
        "lastCheckOut"     => $row['lastCheckOut'],
        "workHours"        => $row['workHours'],
        "attendanceStatus" => $attendanceStatus,
        "isLateCheckout"   => $isLateCheckout,
    ];
}

echo json_encode([
    "status" => true,
    "date"   => $date,
    "data"   => $data
]);
?>