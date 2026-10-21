<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>JM PADDY | Create Bill</title><link rel="stylesheet" href="style.css"></head>
<body><div class="app"><aside class="sidebar"><h2>🌾 JM PADDY</h2><p class="subtitle">Paddy Billing System</p><nav><a href="dashboard.php">🏠 Dashboard</a><a class="active" href="bill.php">🧾 Create Bill</a><a href="history.php">📋 Bill History</a></nav></aside>
<main class="main"><header class="topbar"><div><h1>Create Paddy Bill</h1><p>Enter farmer details and weights. Totals update automatically.</p></div></header>
<form id="billForm" action="save_bill.php" method="POST">
<section class="panel"><h2>👨‍🌾 Farmer Information</h2><div class="grid">
<div><label for="bill_no">Bill Number *</label><input type="text" name="bill_no" id="bill_no" placeholder="e.g. JMP-2026-001" required></div>
<div><label for="bill_date">Date *</label><input type="date" name="bill_date" id="bill_date" value="<?php echo date('Y-m-d'); ?>" required></div>
<div><label for="farmer_name">Farmer Name *</label><input type="text" name="farmer_name" id="farmer_name" required></div>
<div><label for="phone">Phone Number</label><input type="tel" name="phone" id="phone" inputmode="tel" placeholder="Farmer contact number"></div>
<div class="full"><label for="address">Address</label><input type="text" name="address" id="address" placeholder="Farmer address"></div>
<div><label for="district">District *</label><select name="district" id="district" onchange="updateRate()" required><option value="">Select district</option><option>Vavuniya</option><option>Mannar</option><option>Mullaitivu</option><option>Kilinochchi</option><option>Jaffna</option></select></div>
<div><label for="district_rate">District divisor / rate</label><input type="number" name="district_rate" id="district_rate" readonly required></div>
</div></section>
<section class="panel"><h2>🌾 Paddy Information</h2><div class="grid">
<div><label for="paddy_type">Paddy Type *</label><select name="paddy_type" id="paddy_type" required><option value="">Select paddy type</option><option value="Samba">Samba</option><option value="Keeri Samba">Keeri Samba</option><option value="Nadu">Nadu</option></select></div>
<div><label for="price">Price per net kg (Rs.) *</label><input type="number" name="price" id="price" step="0.01" min="0" placeholder="Enter today's price" oninput="calculateBill()" required></div>
</div></section>
<section class="panel"><div class="weight-header"><h2>⚖️ Weight Entries</h2><button type="button" class="small-btn" onclick="addWeightColumn()">＋ Add 10 Weights</button></div><p>Enter each sack or weighing value in kilograms. Additional columns can be added as needed.</p><div id="weightContainer" class="weight-container"></div><input type="hidden" name="weights" id="weightsData"></section>
<section class="panel"><h2>🧮 Bill Calculation</h2><div class="result-grid">
<div class="result-box"><span>Grand Total</span><strong><span id="grandTotal">0.00</span> kg</strong></div>
<div class="result-box"><span>District Divisor</span><strong id="displayRate">—</strong></div>
<div class="result-box"><span>Net Weight</span><strong><span id="netWeight">0.00</span> kg</strong></div>
<div class="result-box total"><span>Total Amount</span><strong>Rs. <span id="totalAmount">0.00</span></strong></div>
</div><input type="hidden" name="total_weight" id="total_weight"><input type="hidden" name="net_weight" id="net_weight"><input type="hidden" name="total_amount" id="total_amount">
<div class="actions"><button type="button" class="calculate" onclick="calculateBill()">Calculate</button><button type="submit" class="save">💾 Save Bill</button><button type="reset" class="clear" onclick="resetWeights()">Clear Form</button></div></section>
</form></main></div><script src="script.js"></script><script>
document.getElementById('billForm').addEventListener('submit',function(e){calculateBill();if(!(parseFloat(document.getElementById('total_weight').value)>0)){e.preventDefault();alert('Please enter at least one weight greater than zero.');}});
</script></body></html>