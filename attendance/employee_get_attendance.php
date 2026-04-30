<?php
header("Content-Type: application/json");
include 'db.php'; // your DB connection

$emp_id = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;
$today = date('Y-m-d');

if(!$emp_id){
    echo json_encode(['status'=>false, 'message'=>'Employee ID required']);
    exit;
}

$sql = "SELECT * FROM attendance WHERE emp_id=? AND date=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $emp_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if($data){
    echo json_encode(['status'=>true, 'data'=>$data]);
} else {
    echo json_encode([
        'status'=>true, 
        'data'=>[
            'emp_id'=>$emp_id,
            'date'=>$today,
            'status'=>'NOT_MARKED',
            'login_time'=>null,
            'logout_time'=>null,
            'login_location_name'=>null,
            'logout_location_name'=>null
        ]
    ]);
}
?>