<?php
require "db.php";
if($_SERVER["REQUEST_METHOD"]!=="POST"){header("Location: farmers.php");exit;}
$id=max(0,(int)($_POST["farmer_id"]??0));$balance=filter_var($_POST["opening_balance"]??null,FILTER_VALIDATE_FLOAT);
if($id<1||$balance===false||$balance<0){http_response_code(422);exit("Enter a valid opening balance. <a href='farmers.php'>Back</a>");}
$q=$conn->prepare("UPDATE farmers SET opening_balance=? WHERE id=?");$q->bind_param("di",$balance,$id);
if(!$q->execute()||$q->affected_rows<0){http_response_code(500);exit("Could not update balance.");}
header("Location: farmers.php?saved=1");exit;
?>