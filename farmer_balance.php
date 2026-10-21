<?php
require "db.php";
header("Content-Type: application/json; charset=utf-8");
$phone=trim((string)($_GET["phone"]??""));
$exclude=max(0,(int)($_GET["exclude"]??0));
if($phone===""){echo json_encode(["balance"=>0]);exit;}
try{
 $q=$conn->prepare("SELECT id,opening_balance FROM farmers WHERE phone=? ORDER BY id DESC LIMIT 1");
 $q->bind_param("s",$phone);$q->execute();$farmer=$q->get_result()->fetch_assoc();
 if(!$farmer){echo json_encode(["balance"=>0]);exit;}
 $sql="SELECT f.opening_balance + COALESCE(SUM(b.total_amount-b.paid_amount-COALESCE(p.paid_extra,0)),0) AS balance
 FROM farmers f LEFT JOIN bills b ON b.farmer_id=f.id AND (?=0 OR b.id<>?)
 LEFT JOIN (SELECT bill_id,SUM(amount) paid_extra FROM bill_payments GROUP BY bill_id) p ON p.bill_id=b.id
 WHERE f.id=? GROUP BY f.id,f.opening_balance";
 $s=$conn->prepare($sql);$s->bind_param("iii",$exclude,$exclude,$farmer["id"]);$s->execute();$row=$s->get_result()->fetch_assoc();
 echo json_encode(["balance"=>max(0,round((float)($row["balance"]??0),2))]);
}catch(Throwable $e){http_response_code(500);echo json_encode(["error"=>"Balance lookup failed","balance"=>0]);}
?>