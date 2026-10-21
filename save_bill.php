<?php
require "db.php";
if($_SERVER["REQUEST_METHOD"]!=="POST"){header("Location: bill.php");exit;}
function field($k){return trim((string)($_POST[$k]??""));}
$editId=max(0,(int)($_GET["edit"]??0));
$billNo=field("bill_no");$date=field("bill_date");$name=field("farmer_name");$phone=field("phone");$address=field("address");$district=field("district");$type=field("paddy_type");
$weights=json_decode((string)($_POST["weights"]??"[]"),true);
if($billNo===""||$date===""||$name===""||$district===""||$type===""||!is_array($weights)){http_response_code(422);exit("Please complete required fields. <a href='bill.php'>Return</a>");}
$rates=["Vavuniya"=>72,"Mannar"=>73,"Mullaitivu"=>73,"Kilinochchi"=>73,"Jaffna"=>73];
$rateQuery=$conn->query("SELECT district,divisor FROM district_rates");
if($rateQuery)while($r=$rateQuery->fetch_assoc())$rates[$r["district"]]=(float)$r["divisor"];
if(!isset($rates[$district])||$rates[$district]<=0){http_response_code(422);exit("Invalid district divisor. Update district rates first.");}
$price=filter_var($_POST["price"]??null,FILTER_VALIDATE_FLOAT);$paid=filter_var($_POST["paid_amount"]??0,FILTER_VALIDATE_FLOAT);
if($price===false||$price<0||$paid===false||$paid<0){http_response_code(422);exit("Enter a valid price and payment amount. <a href='bill.php'>Return</a>");}
$total=0;$clean=[];
foreach($weights as $w){if(!isset($w["number"],$w["weight"])||!is_numeric($w["weight"]))continue;$n=(int)$w["number"];$v=(float)$w["weight"];if($n>0&&$v>0){$total+=$v;$clean[]=["number"=>$n,"weight"=>$v];}}
if($total<=0){http_response_code(422);exit("Enter at least one positive weight. <a href='bill.php'>Return</a>");}
$rate=(float)$rates[$district];$net=$total/$rate;$amount=round($net*(float)$price,2);
try{
 $conn->begin_transaction();
 if($editId>0){$ex=$conn->prepare("SELECT id,farmer_id FROM bills WHERE id=? FOR UPDATE");$ex->bind_param("i",$editId);$ex->execute();$editing=$ex->get_result()->fetch_assoc();if(!$editing)throw new RuntimeException("Bill not found.");}
 $dup=$conn->prepare("SELECT id FROM bills WHERE bill_no=? AND id<>? LIMIT 1");$dup->bind_param("si",$billNo,$editId);$dup->execute();if($dup->get_result()->num_rows)throw new RuntimeException("That bill number already exists. Use a unique bill number.");
 $farmerId=0;
 if($editId>0 && $phone===""){ $farmerId=(int)$editing["farmer_id"]; }
 if($farmerId===0 && $phone!==""){$f=$conn->prepare("SELECT id FROM farmers WHERE phone=? ORDER BY id DESC LIMIT 1");$f->bind_param("s",$phone);$f->execute();$x=$f->get_result()->fetch_assoc();if($x)$farmerId=(int)$x["id"];}
 if($farmerId===0){$f=$conn->prepare("SELECT id FROM farmers WHERE name=? AND address=? ORDER BY id DESC LIMIT 1");$f->bind_param("ss",$name,$address);$f->execute();$x=$f->get_result()->fetch_assoc();if($x)$farmerId=(int)$x["id"];}
 if($farmerId){$f=$conn->prepare("UPDATE farmers SET name=?,phone=?,address=? WHERE id=?");$f->bind_param("sssi",$name,$phone,$address,$farmerId);$f->execute();}
 else{$f=$conn->prepare("INSERT INTO farmers(name,phone,address,opening_balance) VALUES(?,?,?,0)");$f->bind_param("sss",$name,$phone,$address);$f->execute();$farmerId=$conn->insert_id;}
 $balSql="SELECT f.opening_balance + COALESCE(SUM(b.total_amount-b.paid_amount-COALESCE(p.extra_paid,0)),0) AS outstanding FROM farmers f LEFT JOIN bills b ON b.farmer_id=f.id AND (?=0 OR b.id<>?) LEFT JOIN (SELECT bill_id,SUM(amount) extra_paid FROM bill_payments GROUP BY bill_id) p ON p.bill_id=b.id WHERE f.id=? GROUP BY f.id,f.opening_balance";
 $b=$conn->prepare($balSql);$b->bind_param("iii",$editId,$editId,$farmerId);$b->execute();$br=$b->get_result()->fetch_assoc();$previous=max(0,round((float)($br["outstanding"]??0),2));
 $extraPaid=0;
 if($editId>0){$ep=$conn->prepare("SELECT COALESCE(SUM(amount),0) AS paid FROM bill_payments WHERE bill_id=?");$ep->bind_param("i",$editId);$ep->execute();$extraPaid=(float)($ep->get_result()->fetch_assoc()["paid"]??0);}
 if($paid+$extraPaid>$previous+$amount+0.005)throw new RuntimeException("Total payments cannot exceed previous balance plus the updated bill amount.");
 $due=round($previous+$amount-$paid-$extraPaid,2);
 if($editId>0){
  $s=$conn->prepare("UPDATE bills SET bill_no=?,farmer_id=?,bill_date=?,district=?,district_rate=?,paddy_type=?,total_weight=?,net_weight=?,price=?,total_amount=?,paid_amount=?,previous_balance=?,balance_due=? WHERE id=?");
  $s->bind_param("sissdsdddddddi",$billNo,$farmerId,$date,$district,$rate,$type,$total,$net,$price,$amount,$paid,$previous,$due,$editId);$s->execute();
  $del=$conn->prepare("DELETE FROM bill_weights WHERE bill_id=?");$del->bind_param("i",$editId);$del->execute();$billId=$editId;
 }else{
  $s=$conn->prepare("INSERT INTO bills(bill_no,farmer_id,bill_date,district,district_rate,paddy_type,total_weight,net_weight,price,total_amount,paid_amount,previous_balance,balance_due) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $s->bind_param("sissdsddddddd",$billNo,$farmerId,$date,$district,$rate,$type,$total,$net,$price,$amount,$paid,$previous,$due);$s->execute();$billId=$conn->insert_id;
 }
 $wi=$conn->prepare("INSERT INTO bill_weights(bill_id,weight_no,weight) VALUES(?,?,?)");
 foreach($clean as $w){$n=$w["number"];$v=$w["weight"];$wi->bind_param("iid",$billId,$n,$v);$wi->execute();}
 $conn->commit();header("Location: view_bill.php?id=".$billId."&saved=1");exit;
}catch(Throwable $e){$conn->rollback();http_response_code(500);$msg=htmlspecialchars($e->getMessage(),ENT_QUOTES,"UTF-8");echo "<!doctype html><html><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><body style='font:16px Arial;padding:30px'><h2>Unable to save bill</h2><p>".$msg."</p><p>Run <a href='upgrade_v2.sql'>the database upgrade</a> in phpMyAdmin, then <a href='bill.php'>return to the bill form</a>.</p></body></html>";}
?>