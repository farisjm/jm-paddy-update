<?php
$id=max(0,(int)($_GET["id"]??0));
if($id<1){header("Location: history.php");exit;}
header("Location: bill.php?edit=".$id);exit;
?>