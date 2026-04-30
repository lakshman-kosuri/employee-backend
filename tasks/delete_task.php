<?php

require_once "../config/cors.php";
include("../config/database.php");

// Read JSON data from React
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? '';

if(!$id){
 echo json_encode(["error"=>"Task id missing"]);
 exit();
}

$sql = "DELETE FROM tasks WHERE id='$id'";

if($conn->query($sql)){
 echo json_encode(["message"=>"Task deleted"]);
}else{
 echo json_encode(["error"=>$conn->error]);
}

?>