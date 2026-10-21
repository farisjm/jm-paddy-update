<?php
require "db.php";
$editId = isset($_GET["edit"]) ? (int)$_GET["edit"] : 0;
$existing = null;
$initialWeights = [];
if ($editId > 0) {
  $stmt=$conn->prepare("SELECT b.*, f.name AS farmer_name, f.phone, f.address FROM bills b JOIN farmers f ON f.id=b.farmer_id WHERE b.id=?");
  $stmt->bind_param("i",$editId); $stmt->execute(); $existing=$stmt->get_result()->fetch_assoc();
  if (!$existing) { http_response_code(404); exit("Bill not found."); }
  $ws=$conn->prepare("SELECT weight_no AS number, weight FROM bill_weights WHERE bill_id=? ORDER BY weight_no");
  $ws->bind_param("i",$editId); $ws->execute(); $wr=$ws->get_result();
  while($w=$wr->fetch_assoc()) $initialWeights[]=["number"=>(int)$w["number"],"weight"=>(float)$w["weight"]];
}
$rates=["Vavuniya"=>72,"Mannar"=>73,"Mullaitivu"=>73,"Kilinochchi"=>73,"Jaffna"=>73];
$rateResult=$conn->query("SELECT district,divisor FROM district_rates");
if($rateResult) while($r=$rateResult->fetch_assoc()) $rates[$r["district"]]=(float)$r["divisor"];
$paddyPrices=["Samba"=>0,"Keeri Samba"=>0,"Nadu"=>0];
$priceResult=$conn->query("SELECT paddy_type,price FROM paddy_prices");
if($priceResult) while($r=$priceResult->fetch_assoc()) $paddyPrices[$r["paddy_type"]]=(float)$r["price"];
$selectedDistrict=$existing["district"]??"";
$selectedType=$existing["paddy_type"]??"";
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>JM PADDY | <?= $existing ? "Edit Bill" : "Create Bill" ?></title><link rel="stylesheet" href="style.css"></head>
<body><div class="app"><aside class="sidebar"><h2 class="brand"><img src="logo.svg" alt="" /> JM PADDY</h2><p class="subtitle">Paddy Billing System</p><nav><a href="dashboard.php">🏠 Dashboard</a><a class="active" href="bill.php">🧾 <?= $existing ? "Edit Bill" : "Create Bill" ?></a><a href="farmers.php">👨‍🌾 Farmers</a><a href="history.php">📋 Bill History</a><a href="reports.php">📈 Reports</a><a href="prices.php">💰 Rates & Prices</a><a href="settings.php">⚙️ Settings</a></nav></aside>
<main class="main"><header class="topbar"><div><h1><?= $existing ? "Edit Paddy Bill" : "Create Paddy Bill" ?></h1><p><?= $existing ? "Update bill details and save your changes." : "Quick billing with automatic weight, rate and balance calculations." ?></p></div><a class="btn" href="history.php">Bill History →</a></header>
<?php if(isset($_GET["error"])): ?><div class="panel" style="border-color:#f1b5a9;color:#8c2e20">Unable to save the bill. Check the database upgrade and bill details.</div><?php endif; ?>
<form id="billForm" action="save_bill.php<?= $existing ? "?edit=".(int)$editId : "" ?>" method="POST">
<section class="panel"><h2>👨‍🌾 Farmer & Bill Details</h2><div class="grid">
<div><label for="bill_no">Bill Number *</label><input type="text" name="bill_no" id="bill_no" value="<?= htmlspecialchars($existing["bill_no"]??"") ?>" placeholder="e.g. JMP-2026-001" required></div>
<div><label for="bill_date">Date *</label><input type="date" name="bill_date" id="bill_date" value="<?= htmlspecialchars($existing["bill_date"]??date("Y-m-d")) ?>" required></div>
<div><label for="farmer_name">Farmer Name *</label><input type="text" name="farmer_name" id="farmer_name" value="<?= htmlspecialchars($existing["farmer_name"]??"") ?>" required></div>
<div><label for="phone">Phone Number</label><input type="tel" name="phone" id="phone" inputmode="tel" value="<?= htmlspecialchars($existing["phone"]??"") ?>" placeholder="WhatsApp number" onblur="loadFarmerBalance()"></div>
<div class="full"><label for="address">Address</label><input type="text" name="address" id="address" value="<?= htmlspecialchars($existing["address"]??"") ?>" placeholder="Farmer address"></div>
<div><label for="district">District *</label><select name="district" id="district" onchange="updateRate()" required><option value="">Select district</option><?php foreach($rates as $d=>$v): ?><option value="<?= htmlspecialchars($d) ?>" <?= $selectedDistrict===$d?"selected":"" ?>><?= htmlspecialchars($d) ?></option><?php endforeach; ?></select></div>
<div><label for="district_rate">District divisor / rate</label><input type="number" name="district_rate" id="district_rate" value="<?= htmlspecialchars((string)($existing["district_rate"]??($rates[$selectedDistrict]??""))) ?>" readonly required></div>
</div></section>
<section class="panel"><h2>🌾 Paddy Information</h2><div class="grid">
<div><label for="paddy_type">Paddy Type *</label><select name="paddy_type" id="paddy_type" onchange="suggestPaddyPrice()" required><option value="">Select paddy type</option><?php foreach($paddyPrices as $type=>$p): ?><option value="<?= htmlspecialchars($type) ?>" data-price="<?= $p ?>" <?= $selectedType===$type?"selected":"" ?>><?= htmlspecialchars($type) ?></option><?php endforeach; ?></select></div>
<div><label for="price">Price per net kg (Rs.) *</label><input type="number" name="price" id="price" step="0.01" min="0" value="<?= htmlspecialchars((string)($existing["price"]??"")) ?>" placeholder="Enter today's price" oninput="calculateBill()" required></div>
<div><label for="paid_amount">Payment received now (Rs.)</label><input type="number" name="paid_amount" id="paid_amount" min="0" step="0.01" value="<?= htmlspecialchars((string)($existing["paid_amount"]??"0")) ?>" oninput="calculateBill()" required><small>Enter what the farmer pays for this bill now.</small></div>
</div></section>
<section class="panel"><div class="weight-header"><h2>⚖️ Weight Entries</h2><button type="button" class="small-btn" onclick="addWeightColumn()">＋ Add 10 Weights</button></div><p>Enter each sack/weight in kilograms. New columns are added when a group of 10 is filled.</p><div id="weightContainer" class="weight-container"></div><input type="hidden" name="weights" id="weightsData"></section>
<section class="panel"><h2>🧮 Bill & Balance Summary</h2><div class="result-grid">
<div class="result-box"><span>Grand Total</span><strong><span id="grandTotal">0.00</span> kg</strong></div>
<div class="result-box"><span>District Divisor</span><strong id="displayRate"><?= htmlspecialchars((string)($rates[$selectedDistrict]??"—")) ?></strong></div>
<div class="result-box"><span>Net Weight</span><strong><span id="netWeight">0.00</span> kg</strong></div>
<div class="result-box total"><span>This Bill Amount</span><strong>Rs. <span id="totalAmount">0.00</span></strong></div>
<div class="result-box"><span>Previous Outstanding</span><strong>Rs. <span id="previousBalance">0.00</span></strong></div>
<div class="result-box"><span>Total Payable</span><strong>Rs. <span id="totalPayable">0.00</span></strong></div>
<div class="result-box"><span>Paid Now</span><strong>Rs. <span id="paidDisplay">0.00</span></strong></div>
<div class="result-box total"><span>Balance Remaining</span><strong>Rs. <span id="balanceDue">0.00</span></strong></div>
</div><input type="hidden" name="total_weight" id="total_weight"><input type="hidden" name="net_weight" id="net_weight"><input type="hidden" name="total_amount" id="total_amount"><input type="hidden" name="balance_due" id="balance_due"><input type="hidden" id="previous_balance" name="previous_balance" value="0">
<div class="actions"><button type="button" class="calculate" onclick="calculateBill()">Recalculate</button><button type="submit" class="save">💾 <?= $existing ? "Update Bill" : "Save Bill" ?></button><button type="reset" class="clear" onclick="resetWeights()">Clear Form</button><?php if($existing): ?><a class="btn" href="view_bill.php?id=<?= (int)$editId ?>">Cancel</a><?php endif; ?></div>
</section></form></main></div>
<script>window.JMPADDY_RATES=<?= json_encode($rates,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;window.JMPADDY_INITIAL_WEIGHTS=<?= json_encode($initialWeights,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;window.JMPADDY_EDIT_ID=<?= (int)$editId ?>;window.JMPADDY_EXTRA_PAID=<?= json_encode($existingExtraPaid) ?>;</script><script src="script.js"></script>
<script>document.getElementById('billForm').addEventListener('submit',function(e){calculateBill();if(!(parseFloat(document.getElementById('total_weight').value)>0)){e.preventDefault();alert('Please enter at least one weight greater than zero.');}});window.addEventListener('load',function(){if(document.getElementById('district').value)updateRate();if(document.getElementById('phone').value)loadFarmerBalance();calculateBill();});</script></body></html>