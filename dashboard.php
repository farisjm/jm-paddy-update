<?php
require "db.php";
function countRows($conn, $table) {
    $allowed = ["bills", "farmers"];
    if (!in_array($table, $allowed, true)) return 0;
    $result = $conn->query("SELECT COUNT(*) AS total FROM $table");
    return $result ? (int)$result->fetch_assoc()["total"] : 0;
}
$billCount = countRows($conn, "bills");
$farmerCount = countRows($conn, "farmers");
$amountTotal = 0;
$result = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS total FROM bills");
if ($result) $amountTotal = (float)$result->fetch_assoc()["total"];
$outstanding = 0;
$result = $conn->query("SELECT COALESCE(SUM(opening_balance),0) AS total FROM farmers");
if ($result) $outstanding = (float)$result->fetch_assoc()["total"];
$result = $conn->query("SELECT COALESCE(SUM(b.total_amount-b.paid_amount-COALESCE(p.extra_paid,0)),0) AS total FROM bills b LEFT JOIN (SELECT bill_id,SUM(amount) extra_paid FROM bill_payments GROUP BY bill_id) p ON p.bill_id=b.id");
if ($result) $outstanding += (float)$result->fetch_assoc()["total"];
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>JM PADDY | Dashboard</title><link rel="stylesheet" href="style.css"></head>
<body><div class="app">
<aside class="sidebar"><h2>🌾 JM PADDY</h2><p class="subtitle">Paddy Billing System</p><nav><a href="dashboard.php">🏠 Dashboard</a><a href="bill.php">🧾 Create Bill</a><a href="farmers.php">👨‍🌾 Farmers</a><a href="history.php">📋 Bill History</a><a href="reports.php">📈 Reports</a><a href="prices.php">💰 Price Management</a><a href="settings.php">⚙️ Settings</a></nav></aside>
<main class="main"><header class="topbar"><div><h1>Dashboard</h1><p>Welcome back — here's your paddy business overview.</p></div><a class="btn" href="bill.php">＋ New Bill</a></header>
<section class="cards">
<div class="card"><h3>🧾 Total Bills</h3><strong style="font-size:30px;color:#174b30"><?= number_format($billCount) ?></strong><p>Saved billing records</p></div>
<div class="card"><h3>👨‍🌾 Farmers</h3><strong style="font-size:30px;color:#174b30"><?= number_format($farmerCount) ?></strong><p>Farmer records in database</p></div>
<div class="card"><h3>🌾 Total Bill Value</h3><strong style="font-size:clamp(20px,2.5vw,30px);color:#174b30">Rs. <?= number_format($amountTotal,2) ?></strong><p>Combined value of saved bills</p></div>
<div class="card"><h3>💵 Outstanding Balance</h3><strong style="font-size:clamp(20px,2.5vw,30px);color:#a93636">Rs. <?= number_format(max(0,$outstanding),2) ?></strong><p>Opening balances plus unpaid bills</p></div>
</section>
<section class="welcome"><h2>Simple • Fast • Accurate</h2><p>Manage paddy weights, calculate net weight using district rates, and keep your bills organized in one place.</p><a class="btn" href="bill.php">＋ Create New Bill</a> <a class="btn" style="background:#329b5d" href="history.php">View Bill History →</a></section>
<section class="panel" style="margin-top:22px"><h2>Quick workflow</h2><div class="cards"><div><b>01 · Farmer details</b><p>Enter the farmer's name, phone and address.</p></div><div><b>02 · Weight & price</b><p>Enter weights and price to calculate the bill.</p></div><div><b>03 · Save & print</b><p>Save the bill, open its details and print or save as PDF.</p></div></div></section>
</main></div></body></html>