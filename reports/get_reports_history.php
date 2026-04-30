<?php

require_once "../config/cors.php";
include("../config/database.php");

$query = "SELECT * FROM reports_history ORDER BY created_at DESC LIMIT 10";

$result = $conn->query($query);

$reports = [];

while($row = $result->fetch_assoc()){
    $reports[] = $row;
}

echo json_encode($reports);