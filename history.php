<?php
require "db.php";
$sql = "SELECT bills.*, farmers.name AS farmer_name FROM bills INNER JOIN farmers ON bills.farmer_id = farmers.id ORDER BY bills.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>JM PADDY | Bill History</title><link rel="stylesheet" href="style.css"></head>
<body><div class="app"><aside class="sidebar"><h2>🌾 JM PADDY</h2><p class="subtitle">Paddy Billing System</p><nav><a href="dashboard.php">🏠 Dashboard</a><a href="bill.php">🧾 Create Bill</a><a href="farmers.php">👨‍🌾 Farmers</a><a href="history.php">📋 Bill History</a><a href="reports.php">📈 Reports</a><a href="prices.php">💰 Price Management</a><a href="settings.php">⚙️ Settings</a></nav></aside>
<main class="main"><header class="topbar"><div><h1>Bill History</h1><p>Review saved paddy bills and open printable receipts.</p></div><a class="btn" href="bill.php">＋ New Bill</a></header>
<section class="panel"><div style="overflow-x:auto"><table><thead><tr><th>Bill No.</th><th>Date</th><th>Farmer</th><th>District</th><th>Paddy</th><th>Total kg</th><th>Net kg</th><th>Amount</th><th>View</th></tr></thead><tbody>
<?php if ($result && $result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
<tr><td><strong><?= htmlspecialchars($row["bill_no"]) ?></strong></td><td><?= htmlspecialchars($row["bill_date"]) ?></td><td><?= htmlspecialchars($row["farmer_name"]) ?></td><td><?= htmlspecialchars($row["district"]) ?></td><td><?= htmlspecialchars($row["paddy_type"]) ?></td><td><?= number_format((float)$row["total_weight"],2) ?></td><td><?= number_format((float)$row["net_weight"],2) ?></td><td><strong>Rs. <?= number_format((float)$row["total_amount"],2) ?></strong></td><td><a class="btn" style="padding:7px 10px;font-size:12px" href="view_bill.php?id=<?= (int)$row["id"] ?>">Open bill ↗</a></td></tr>
<?php endwhile; else: ?><tr><td colspan="9" style="text-align:center;padding:30px">No bills yet. Create your first bill to see it here.</td></tr><?php endif; ?>
</tbody></table></div></section></main></div></body></html>