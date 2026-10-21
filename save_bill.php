<?php
require "db.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: bill.php"); exit; }
function field($key) { return trim((string)($_POST[$key] ?? "")); }
$bill_no=field("bill_no"); $bill_date=field("bill_date"); $farmer_name=field("farmer_name");
$phone=field("phone"); $address=field("address"); $district=field("district"); $paddy_type=field("paddy_type");
$weights=json_decode((string)($_POST["weights"] ?? "[]"),true);
$districts=["Vavuniya"=>72,"Mannar"=>73,"Mullaitivu"=>73,"Kilinochchi"=>73,"Jaffna"=>73];
if($bill_no===""||$bill_date===""||$farmer_name===""||!isset($districts[$district])||$paddy_type===""||!is_array($weights)){
 http_response_code(422); exit("Please fill all required fields. <a href='bill.php'>Return</a>");
}
$price=filter_var($_POST["price"]??null,FILTER_VALIDATE_FLOAT);
$paid=filter_var($_POST["paid_amount"]??0,FILTER_VALIDATE_FLOAT);
if($price===false||$price<0||$paid===false||$paid<0){http_response_code(422);exit("Enter valid price and payment values. <a href='bill.php'>Return</a>");}
$rate=(float)$districts[$district];$total=0;$clean=[];
foreach($weights as $w){if(!isset($w["number"],$w["weight"])||!is_numeric($w["weight"]))continue;$n=(int)$w["number"];$v=(float)$w["weight"];if($n>0&&$v>0){$total+=$v;$clean[]=["number"=>$n,"weight"=>$v];}}
if($total<=0){http_response_code(422);exit("Enter at least one positive weight. <a href='bill.php'>Return</a>");}
$net=$total/$rate;$amount=round($net*(float)$price,2);
try{
 $conn->begin_transaction();
 $dup=$conn->prepare("SELECT id FROM bills WHERE bill_no=? LIMIT 1");$dup->bind_param("s",$bill_no);$dup->execute();
 if($dup->get_result()->num_rows){throw new RuntimeException("That bill number already exists. Please use a unique bill number.");}
 $farmerId=0;
 if($phone!==""){$find=$conn->prepare("SELECT id FROM farmers WHERE phone=? ORDER BY id DESC LIMIT 1");$find->bind_param("s",$phone);$find->execute();$found=$find->get_result()->fetch_assoc();if($found)$farmerId=(int)$found["id"];}
 if(!$farmerId){$find=$conn->prepare("SELECT id FROM farmers WHERE name=? AND address=? ORDER BY id DESC LIMIT 1");$find->bind_param("ss",$farmer_name,$address);$find->execute();$found=$find->get_result()->fetch_assoc();if($found)$farmerId=(int)$found["id"];}
 if($farmerId){$upd=$conn->prepare("UPDATE farmers SET name=?,phone=?,address=? WHERE id=?");$upd->bind_param("sssi",$farmer_name,$phone,$address,$farmerId);$upd->execute();}
 else{$ins=$conn->prepare("INSERT INTO farmers(name,phone,address) VALUES(?,?,?)");$ins->bind_param("sss",$farmer_name,$phone,$address);$ins->execute();$farmerId=$conn->insert_id;}
 $bal=$conn->prepare("SELECT f.opening_balance + COALESCE(SUM(b.total_amount-b.paid_amount),0) AS outstanding FROM farmers f LEFT JOIN bills b ON b.farmer_id=f.id WHERE f.id=? GROUP BY f.id,f.opening_balance");
 $bal->bind_param("i",$farmerId);$bal->execute();$brow=$bal->get_result()->fetch_assoc();$previous=max(0,(float)($brow["outstanding"]??0));
 if($paid>$previous+$amount){throw new RuntimeException("Payment received cannot exceed the previous balance plus this bill total.");}
 $due=round($previous+$amount-$paid,2);
 $insBill=$conn->prepare("INSERT INTO bills(bill_no,farmer_id,bill_date,district,district_rate,paddy_type,total_weight,net_weight,price,total_amount,paid_amount,previous_balance,balance_due) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
 $insBill->bind_param("sissdsddddddd",$bill_no,$farmerId,$bill_date,$district,$rate,$paddy_type,$total,$net,$price,$amount,$paid,$previous,$due);$insBill->execute();$billId=$conn->insert_id;
 $wi=$conn->prepare("INSERT INTO bill_weights(bill_id,weight_no,weight) VALUES(?,?,?)");
 foreach($clean as $w){$n=$w["number"];$v=$w["weight"];$wi->bind_param("iid",$billId,$n,$v);$wi->execute();}
 $conn->commit();header("Location: view_bill.php?id=".$billId."&saved=1");exit;
}catch(Throwable $e){$conn->rollback();http_response_code(500);$msg=htmlspecialchars($e->getMessage(),ENT_QUOTES,"UTF-8");echo "<!doctype html><html><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><body style='font:16px Arial;padding:30px'><h2>Unable to save bill</h2><p>".$msg."</p><p>Confirm you ran <a href='upgrade_balance.sql'>the database upgrade</a>, then <a href='bill.php'>return to the bill form</a>.</p></body></html>";}
?>