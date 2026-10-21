<?php
require "db.php";
if (!isset($_GET["id"]) || !filter_var($_GET["id"], FILTER_VALIDATE_INT)) { http_response_code(400); exit("Bill ID is required."); }
$id = (int)$_GET["id"];
$stmt = $conn->prepare("SELECT bills.*, farmers.name AS farmer_name, farmers.phone, farmers.address FROM bills INNER JOIN farmers ON bills.farmer_id=farmers.id WHERE bills.id=?");
$stmt->bind_param("i", $id); $stmt->execute(); $result = $stmt->get_result();
if (!$result || $result->num_rows === 0) { http_response_code(404); exit("Bill not found."); }
$bill = $result->fetch_assoc();
$weightStmt = $conn->prepare("SELECT weight_no, weight FROM bill_weights WHERE bill_id=? ORDER BY weight_no");
$weightStmt->bind_param("i", $id); $weightStmt->execute(); $weights = $weightStmt->get_result();
$phoneDigits = preg_replace('/[^0-9]/', '', (string)$bill["phone"]);
$message = "JM PADDY - Paddy Bill%0ABill No: " . rawurlencode($bill["bill_no"]) . "%0ADate: " . rawurlencode($bill["bill_date"]) . "%0AFarmer: " . rawurlencode($bill["farmer_name"]) . "%0APaddy: " . rawurlencode($bill["paddy_type"]) . "%0ANet Weight: " . rawurlencode(number_format((float)$bill["net_weight"], 2)) . " kg%0ATotal Amount: Rs. " . rawurlencode(number_format((float)$bill["total_amount"], 2));
$whatsappUrl = "https://wa.me/" . $phoneDigits . "?text=" . $message;
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>JM PADDY | <?= htmlspecialchars($bill["bill_no"]) ?></title>
<style>
:root{--dark:#123b27;--green:#1d633d;--mid:#329b5d;--pale:#e6f5e9;--bg:#f4fbf5;--line:#dce8de}*{box-sizing:border-box}body{margin:0;padding:24px;background:var(--bg);font:15px/1.5 Arial,sans-serif;color:#203126}.bill{max-width:850px;margin:auto;background:#fff;padding:clamp(18px,4vw,38px);border:1px solid #e2ece3;border-radius:16px;box-shadow:0 8px 28px #174b3010}.header{text-align:center;border-bottom:2px solid var(--green);padding-bottom:18px}.logo{font-size:30px;font-weight:800;color:var(--green);margin:0}.header p{margin:4px;color:#5f7064}.info{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:22px}.info-box{padding:12px;background:#f5faf5;border:1px solid #e4eee5;border-radius:8px;overflow-wrap:anywhere}.info-box b{color:var(--dark)}h3{color:var(--dark);margin-top:25px}table{width:100%;border-collapse:collapse;margin-top:12px}th,td{padding:10px;border:1px solid var(--line);text-align:left}th{background:var(--green);color:#fff}.summary{margin:22px 0 0 auto;max-width:360px}.summary div{display:flex;justify-content:space-between;gap:15px;padding:10px;border-bottom:1px solid var(--line)}.grand{font-size:19px;font-weight:bold;background:var(--pale);color:var(--dark)}.actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin:25px 0 0}.btn{display:inline-flex;padding:11px 16px;border-radius:8px;text-decoration:none;border:0;background:var(--green);color:#fff;font-weight:bold;cursor:pointer;font-size:14px}.btn.whatsapp{background:#238447}.btn.secondary{background:#edf6ee;color:var(--dark)}.saved{padding:10px 12px;background:var(--pale);color:var(--dark);border-radius:8px;margin-top:16px;text-align:center}
@media(max-width:560px){body{padding:8px}.bill{padding:16px;border-radius:10px}.info{grid-template-columns:1fr}.summary{max-width:none}th,td{padding:8px;font-size:13px}}
@media print{body{padding:0;background:#fff}.bill{border:0;box-shadow:none;max-width:none;padding:10px}.actions,.saved{display:none!important}th{background:#e6f5e9!important;color:#123b27!important;print-color-adjust:exact}}
</style></head><body><article class="bill">
<header class="header"><p class="logo">🌾 JM PADDY</p><p>Paddy &amp; Farmer Billing System</p><p>Simple • Fast • Accurate</p></header>
<?php if (isset($_GET["saved"])): ?><div class="saved">✓ Bill saved successfully.</div><?php endif; ?>
<section class="info">
<div class="info-box"><b>Bill No:</b> <?= htmlspecialchars($bill["bill_no"]) ?></div><div class="info-box"><b>Date:</b> <?= htmlspecialchars($bill["bill_date"]) ?></div>
<div class="info-box"><b>Farmer:</b> <?= htmlspecialchars($bill["farmer_name"]) ?></div><div class="info-box"><b>Phone:</b> <?= htmlspecialchars($bill["phone"] ?: "—") ?></div>
<div class="info-box"><b>Address:</b> <?= htmlspecialchars($bill["address"] ?: "—") ?></div><div class="info-box"><b>District:</b> <?= htmlspecialchars($bill["district"]) ?></div>
<div class="info-box"><b>Paddy Type:</b> <?= htmlspecialchars($bill["paddy_type"]) ?></div><div class="info-box"><b>District Divisor:</b> <?= number_format((float)$bill["district_rate"], 2) ?></div>
</section>
<h3>⚖️ Weight Details</h3><div style="overflow-x:auto"><table><thead><tr><th>Weight No.</th><th>Weight (kg)</th></tr></thead><tbody>
<?php $hasWeights=false; while ($weight=$weights->fetch_assoc()): $hasWeights=true; ?><tr><td>Weight <?= (int)$weight["weight_no"] ?></td><td><?= number_format((float)$weight["weight"],2) ?> kg</td></tr><?php endwhile; if (!$hasWeights): ?><tr><td colspan="2">No individual weights recorded.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="summary"><div><span>Grand Total</span><strong><?= number_format((float)$bill["total_weight"],2) ?> kg</strong></div><div><span>Net Weight</span><strong><?= number_format((float)$bill["net_weight"],2) ?> kg</strong></div><div><span>Price per net kg</span><strong>Rs. <?= number_format((float)$bill["price"],2) ?></strong></div><div class="grand"><span>Total Amount</span><strong>Rs. <?= number_format((float)$bill["total_amount"],2) ?></strong></div></div>
<div class="actions"><button class="btn" onclick="window.print()">🖨️ Print / Save PDF</button><?php if ($phoneDigits !== ""): ?><a class="btn whatsapp" target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES) ?>">WhatsApp Bill Summary</a><?php endif; ?><a class="btn secondary" href="history.php">Bill History</a><a class="btn secondary" href="bill.php">＋ New Bill</a></div>
</article></body></html>