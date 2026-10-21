<?php
require "db.php";
if($_SERVER["REQUEST_METHOD"]!=="POST"){header("Location: history.php");exit;}
$id=max(0,(int)($_POST["bill_id"]??0));$amount=filter_var($_POST["amount"]??null,FILTER_VALIDATE_FLOAT);
$date=trim((string)($_POST["payment_date"]??date("Y-m-d")));$note=trim((string)($_POST["note"]??""));
if($id<1||$amount===false||$amount<=0){http_response_code(422);exit("Enter a valid payment amount. <a href='history.php'>Back</a>");}
try{
 $conn->begin_transaction();
 $q=$conn->prepare("SELECT total_amount,paid_amount,previous_balance FROM bills WHERE id=? FOR UPDATE");$q->bind_param("i",$id);$q->execute();$bill=$q->get_result()->fetch_assoc();
 if(!$bill)throw new RuntimeException("Bill not found.");
 $p=$conn->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM bill_payments WHERE bill_id=?");$p->bind_param("i",$id);$p->execute();$extra=(float)$p->get_result()->fetch_assoc()["total"];
 $remaining=max(0,(float)$bill["previous_balance"]+(float)$bill["total_amount"]-(float)$bill["paid_amount"]-$extra);
 if($amount>$remaining+0.005)throw new RuntimeException("Payment is higher than the remaining balance (Rs. ".number_format($remaining,2).").");
 $ins=$conn->prepare("INSERT INTO bill_payments(bill_id,amount,payment_date,note) VALUES(?,?,?,?)");$ins->bind_param("idss",$id,$amount,$date,$note);$ins->execute();
 $newDue=round($remaining-$amount,2);$up=$conn->prepare("UPDATE bills SET balance_due=? WHERE id=?");$up->bind_param("di",$newDue,$id);$up->execute();
 $conn->commit();header("Location: view_bill.php?id=".$id."&payment=1");exit;
}catch(Throwable $e){$conn->rollback();http_response_code(422);$msg=htmlspecialchars($e->getMessage(),ENT_QUOTES,"UTF-8");echo "<p>".$msg."</p><p><a href='view_bill.php?id=".$id."'>Return to bill</a></p>";}
?>