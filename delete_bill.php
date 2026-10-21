<?php
require "db.php";
if($_SERVER["REQUEST_METHOD"]!=="POST"){header("Location: history.php");exit;}
$id=max(0,(int)($_POST["id"]??0));
if($id<1){header("Location: history.php");exit;}
try{
 $conn->begin_transaction();
 $q=$conn->prepare("SELECT id FROM bills WHERE id=? FOR UPDATE");$q->bind_param("i",$id);$q->execute();
 if(!$q->get_result()->num_rows)throw new RuntimeException("Bill not found.");
 $p=$conn->prepare("DELETE FROM bill_payments WHERE bill_id=?");$p->bind_param("i",$id);$p->execute();
 $w=$conn->prepare("DELETE FROM bill_weights WHERE bill_id=?");$w->bind_param("i",$id);$w->execute();
 $d=$conn->prepare("DELETE FROM bills WHERE id=?");$d->bind_param("i",$id);$d->execute();
 $conn->commit();header("Location: history.php?deleted=1");exit;
}catch(Throwable $e){$conn->rollback();http_response_code(500);echo "Could not delete this bill. Please check database tables and try again. <a href='history.php'>Back to history</a>";}
?>