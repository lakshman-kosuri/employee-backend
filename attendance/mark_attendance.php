<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include("../config/database.php");

function fail($msg, $extra = []) {
    echo json_encode(array_merge(['status' => false, 'message' => $msg], $extra));
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) fail('Invalid JSON body');

$emp_id   = $data['emp_id']   ?? null;
$date     = $data['date']     ?? date("Y-m-d");
$status   = $data['status']   ?? 'NOT_MARKED';
$sessions = $data['sessions'] ?? null;  // array of {loginTime, logoutTime}

if (!$emp_id) fail('Employee ID required');

// Verify employee
$empCheck = $conn->prepare("SELECT emp_id FROM employees WHERE emp_id = ?");
$empCheck->bind_param("s", $emp_id);
$empCheck->execute();
if (!$empCheck->get_result()->fetch_assoc()) fail('Employee not found');

// ── MULTI-SESSION MODE ────────────────────────────────────────────
if ($sessions !== null && is_array($sessions)) {
    $sessions_json  = json_encode($sessions);
    $total_seconds  = 0;
    $first_login    = null;
    $last_logout    = null;
    $has_open       = false; // session with loginTime but no logoutTime

    foreach ($sessions as $s) {
        $loginT  = $s['loginTime']  ?? null;
        $logoutT = $s['logoutTime'] ?? null;

        if ($loginT && !$first_login) $first_login = $loginT;

        if ($loginT && $logoutT) {
            $in  = strtotime($loginT);
            $out = strtotime($logoutT);
            if ($out > $in) $total_seconds += ($out - $in);
            $last_logout = $logoutT;
        } elseif ($loginT && !$logoutT) {
            $has_open = true; // currently checked in
        }
    }

    // ✅ LATE CHECKOUT DETECTION
    // If there is a last_logout but the employee is NOT currently checked in
    // AND there's no new check-in after the last check-out → flag as LATE_CHECKOUT
    // (meaning they only checked out, never re-checked-in for a new session)
    $isLateCheckout = false;
    if ($last_logout && !$has_open && !$first_login) {
        $isLateCheckout = true;
        // If no check-in exists today at all, treat checkout as standalone
        $status = 'LATE_CHECKOUT';
    }

    // Build work_hours string from total_seconds
    $work_hours = null;
    if ($total_seconds > 0) {
        $h = floor($total_seconds / 3600);
        $m = floor(($total_seconds % 3600) / 60);
        $s2 = $total_seconds % 60;
        $work_hours = "{$h}h {$m}m {$s2}s";
    }

    // Upsert
    $check = $conn->prepare("SELECT id FROM attendance WHERE emp_id=? AND date=?");
    $check->bind_param("ss", $emp_id, $date);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE attendance
            SET status=?, sessions=?, login_time=?, logout_time=?, work_hours=?
            WHERE emp_id=? AND date=?
        ");
        $stmt->bind_param("sssssss",
            $status, $sessions_json,
            $first_login, $last_logout,
            $work_hours,
            $emp_id, $date
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO attendance (emp_id, date, status, sessions, login_time, logout_time, work_hours)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss",
            $emp_id, $date, $status,
            $sessions_json,
            $first_login, $last_logout,
            $work_hours
        );
    }

    if (!$stmt->execute()) fail('Execute failed', ['error' => $stmt->error]);

    // Return saved record
    $fetch = $conn->prepare("SELECT * FROM attendance WHERE emp_id=? AND date=?");
    $fetch->bind_param("ss", $emp_id, $date);
    $fetch->execute();
    $record = $fetch->get_result()->fetch_assoc();

    $saved_sessions = json_decode($record['sessions'] ?? '[]', true) ?: [];

    echo json_encode([
        'status'  => true,
        'message' => 'Attendance saved',
        'data'    => [
            'emp_id'        => $record['emp_id'],
            'date'          => $record['date'],
            'status'        => $record['status'],
            'sessions'      => $saved_sessions,
            'loginTime'     => $record['login_time'],
            'logoutTime'    => $record['logout_time'],
            'workHours'     => $record['work_hours'],
            'isLateCheckout'=> $isLateCheckout,
        ]
    ]);
    exit;
}

// ── LEGACY SINGLE-SESSION MODE ────────────────────────────────────
$login_time      = $data['loginTime']         ?? null;
$logout_time     = $data['logoutTime']        ?? null;
$login_location  = $data['loginLocationName'] ?? null;
$logout_location = $data['logoutLocationName']?? null;

$work_hours = null;
if ($login_time && $logout_time) {
    $in  = strtotime($login_time);
    $out = strtotime($logout_time);
    if ($out > $in) {
        $diff = $out - $in;
        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        $s2 = $diff % 60;
        $work_hours = "{$h}h {$m}m {$s2}s";
    }
}

// ✅ Late checkout detection for legacy mode
if ($logout_time && !$login_time) {
    $status = 'LATE_CHECKOUT';
}

$check = $conn->prepare("SELECT id FROM attendance WHERE emp_id=? AND date=?");
$check->bind_param("ss", $emp_id, $date);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare("
        UPDATE attendance
        SET status=?, login_time=?, logout_time=?,
            login_location_name=?, logout_location_name=?, work_hours=?
        WHERE emp_id=? AND date=?
    ");
    $stmt->bind_param("ssssssss",
        $status, $login_time, $logout_time,
        $login_location, $logout_location, $work_hours,
        $emp_id, $date
    );
} else {
    $stmt = $conn->prepare("
        INSERT INTO attendance (emp_id, date, status, login_time, logout_time,
                                login_location_name, logout_location_name, work_hours)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssssss",
        $emp_id, $date, $status,
        $login_time, $logout_time,
        $login_location, $logout_location, $work_hours
    );
}

if (!$stmt->execute()) fail('Execute failed', ['error' => $stmt->error]);

$fetch = $conn->prepare("SELECT * FROM attendance WHERE emp_id=? AND date=?");
$fetch->bind_param("ss", $emp_id, $date);
$fetch->execute();
$record = $fetch->get_result()->fetch_assoc();

echo json_encode([
    'status'  => true,
    'message' => 'Attendance saved',
    'data'    => [
        'emp_id'             => $record['emp_id'],
        'date'               => $record['date'],
        'status'             => $record['status'],
        'loginTime'          => $record['login_time'],
        'logoutTime'         => $record['logout_time'],
        'workHours'          => $record['work_hours'],
        'isLateCheckout'     => ($record['status'] === 'LATE_CHECKOUT'),
        'loginLocationName'  => $record['login_location_name'],
        'logoutLocationName' => $record['logout_location_name'],
    ]
]);
?>