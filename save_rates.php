<?php
require "db.php";
if($_SERVER["REQUEST_METHOD"]!=="POST"){header("Location: prices.php");exit;}
$kind=(string)($_POST["kind"]??"");
try{
 if($kind==="rates"){
  $valid=["Ampara","Anuradhapura","Badulla","Batticaloa","Colombo","Galle","Gampaha","Hambantota","Jaffna","Kalutara","Kandy","Kegalle","Kilinochchi","Kurunegala","Mannar","Matale","Matara","Monaragala","Mullaitivu","Nuwara Eliya","Polonnaruwa","Puttalam","Ratnapura","Trincomalee","Vavuniya"];$conn->begin_transaction();
  $s=$conn->prepare("INSERT INTO district_rates(district,divisor) VALUES(?,?) ON DUPLICATE KEY UPDATE divisor=VALUES(divisor)");
  foreach(($_POST["rates"]??[]) as $district=>$value){$value=filter_var($value,FILTER_VALIDATE_FLOAT);if(!in_array($district,$valid,true)||$value===false||$value<=0)continue;$s->bind_param("sd",$district,$value);$s->execute();}
  $conn->commit();
 }elseif($kind==="prices"){
  $valid=["Samba","Keeri Samba","Nadu"];$conn->begin_transaction();
  $s=$conn->prepare("INSERT INTO paddy_prices(paddy_type,price) VALUES(?,?) ON DUPLICATE KEY UPDATE price=VALUES(price)");
  foreach(($_POST["prices"]??[]) as $type=>$value){$value=filter_var($value,FILTER_VALIDATE_FLOAT);if(!in_array($type,$valid,true)||$value===false||$value<0)continue;$s->bind_param("sd",$type,$value);$s->execute();}
  $conn->commit();
 }else{throw new RuntimeException("Unknown settings type.");}
 header("Location: prices.php?saved=1");exit;
}catch(Throwable $e){if($conn->errno===0){}$conn->rollback();http_response_code(500);echo "Could not save rates. Please import <a href='upgrade_v2.sql'>upgrade_v2.sql</a> in phpMyAdmin, then retry. <a href='prices.php'>Back</a>";}
?>