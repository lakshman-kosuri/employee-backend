<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/csv");

include("../config/database.php");

$date = $_GET['date'];

$query = "
SELECT 
  e.name AS Employee,
  a.date AS Date,
  a.status AS Status,
  a.login_time AS Login_Time,
  a.logout_time AS Logout_Time,
  a.login_location_name AS Login_Location,
  a.logout_location_name AS Logout_Location,
  a.work_hours AS Work_Hours
FROM attendance a
LEFT JOIN employees e ON a.emp_id = e.emp_id
WHERE DATE_FORMAT(a.date, '%Y-%m') = '$date'
";

$result = $conn->query($query);

// Debug
if (!$result) {
    die("SQL Error: " . $conn->error);
}

$output = fopen("php://output", "w");

// ✅ Updated CSV headers
fputcsv($output, [
  "Employee",
  "Date",
  "Status",
  "Login Time",
  "Logout Time",
  "Login Location",
  "Logout Location",
  "Work Hours"
]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;