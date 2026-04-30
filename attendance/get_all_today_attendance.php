<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/database.php");

$today = date("Y-m-d");

$query = "
SELECT e.id, e.emp_id, e.name,
       a.status, a.login_time, a.logout_time,
       a.login_location_name, a.logout_location_name
FROM employees e
LEFT JOIN attendance a 
    ON e.emp_id = a.emp_id AND a.date = ?
ORDER BY e.id ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$employees = [];

while ($row = $result->fetch_assoc()) {
    $employees[] = [
        "id" => $row["id"],
        "emp_id" => $row["emp_id"],
        "name" => $row["name"],
        "status" => $row["status"] ?? "NOT_MARKED",
        "loginTime" => $row["login_time"],
        "logoutTime" => $row["logout_time"],
        "loginLocationName" => $row["login_location_name"],
        "logoutLocationName" => $row["logout_location_name"]
    ];
}

echo json_encode([
    "status" => true,
    "data" => $employees
]);

$conn->close();
?>