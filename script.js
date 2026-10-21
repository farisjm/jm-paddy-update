let columnCount = 1;
let restoringWeights = false;

function addWeightColumn() {
  const container = document.getElementById("weightContainer");
  if (!container) return;
  const start = (columnCount - 1) * 10 + 1;
  const end = columnCount * 10;
  const column = document.createElement("div");
  column.className = "weight-column";
  let html = '<h3>Weights ' + start + ' - ' + end + '</h3>';
  for (let i = start; i <= end; i++) {
    html += '<div class="weight-row"><label for="weight_' + i + '">' + i +
      '</label><input id="weight_' + i + '" type="number" step="0.01" min="0" ' +
      'inputmode="decimal" class="weight-input" data-number="' + i +
      '" aria-label="Weight ' + i + ' in kilograms"></div>';
  }
  html += '<div class="subtotal">Column Total: <strong><span class="column-total">0.00</span> kg</strong></div>';
  column.innerHTML = html;
  container.appendChild(column);
  columnCount++;
}

function weightChanged(input) {
  calculateColumnTotal(input.closest(".weight-column"));
  if (!restoringWeights) autoAddNextColumn();
}

function calculateColumnTotal(column) {
  if (!column) return;
  let total = 0;
  column.querySelectorAll(".weight-input").forEach(input => {
    total += Math.max(0, Number(input.value) || 0);
  });
  const output = column.querySelector(".column-total");
  if (output) output.textContent = total.toFixed(2);
  calculateBill();
}

function autoAddNextColumn() {
  const columns = document.querySelectorAll(".weight-column");
  if (!columns.length) return;
  const last = columns[columns.length - 1];
  const inputs = Array.from(last.querySelectorAll(".weight-input"));
  if (inputs.length === 10 && inputs.every(input => input.value !== "" && Number(input.value) > 0)) {
    addWeightColumn();
  }
}

function calculateGrandTotal() {
  let total = 0;
  document.querySelectorAll(".weight-input").forEach(input => {
    total += Math.max(0, Number(input.value) || 0);
  });
  const output = document.getElementById("grandTotal");
  const hidden = document.getElementById("total_weight");
  if (output) output.textContent = total.toLocaleString("en-LK", {minimumFractionDigits: 2, maximumFractionDigits: 2});
  if (hidden) hidden.value = total.toFixed(2);
  return total;
}

function updateRate() {
  const district = document.getElementById("district")?.value || "";
  const rate = Number((window.JMPADDY_RATES || {})[district] || 0);
  const field = document.getElementById("district_rate");
  if (field) field.value = rate || "";
  const display = document.getElementById("displayRate");
  if (display) display.textContent = rate ? rate.toFixed(2) : "—";
  calculateBill();
}

function suggestPaddyPrice() {
  const select = document.getElementById("paddy_type");
  if (!select) return;
  const option = select.options[select.selectedIndex];
  const suggested = Number(option?.dataset?.price || 0);
  const price = document.getElementById("price");
  if (price && suggested > 0) price.value = suggested;
  calculateBill();
}

function calculateBill() {
  const total = calculateGrandTotal();
  const rate = Number(document.getElementById("district_rate")?.value || 0);
  const price = Number(document.getElementById("price")?.value || 0);
  const paid = Number(document.getElementById("paid_amount")?.value || 0);
  const net = rate > 0 ? total / rate : 0;
  const amount = Math.round((net * price + Number.EPSILON) * 100) / 100;
  const previous = Number(document.getElementById("previous_balance")?.value || 0);
  const extraPaid = Number(window.JMPADDY_EXTRA_PAID || 0);
  const due = Math.max(0, Math.round((previous + amount - paid - extraPaid + Number.EPSILON) * 100) / 100);
  const format = value => Number(value).toLocaleString("en-LK", {minimumFractionDigits: 2, maximumFractionDigits: 2});
  const set = (id, value) => { const element = document.getElementById(id); if (element) element.textContent = format(value); };
  set("netWeight", net);
  set("totalAmount", amount);
  set("previousBalance", previous);
  set("totalPayable", previous + amount);
  set("paidDisplay", paid + extraPaid);
  set("balanceDue", due);
  const netField = document.getElementById("net_weight");
  const amountField = document.getElementById("total_amount");
  const dueField = document.getElementById("balance_due");
  if (netField) netField.value = net.toFixed(4);
  if (amountField) amountField.value = amount.toFixed(2);
  if (dueField) dueField.value = due.toFixed(2);
  const weights = [];
  document.querySelectorAll(".weight-input").forEach(input => {
    const value = Number(input.value) || 0;
    if (input.value.trim() !== "" && value > 0) {
      weights.push({number: Number(input.dataset.number), weight: value});
    }
  });
  const weightsField = document.getElementById("weightsData");
  if (weightsField) weightsField.value = JSON.stringify(weights);
  return {total, weights};
}

async function loadFarmerBalance() {
  const phone = document.getElementById("phone")?.value.trim() || "";
  const field = document.getElementById("previous_balance");
  if (!field) return;
  if (!phone) {
    field.value = "0";
    calculateBill();
    return;
  }
  try {
    const edit = Number(window.JMPADDY_EDIT_ID || 0);
    const response = await fetch("farmer_balance.php?phone=" + encodeURIComponent(phone) + "&exclude=" + edit, {headers: {"Accept": "application/json"}});
    if (!response.ok) return;
    const data = await response.json();
    field.value = Number(data.balance || 0).toFixed(2);
    calculateBill();
  } catch (error) {
    console.warn("Could not refresh farmer balance", error);
  }
}

function resetWeights() {
  setTimeout(() => {
    const container = document.getElementById("weightContainer");
    if (container) container.innerHTML = "";
    columnCount = 1;
    addWeightColumn();
    calculateBill();
  }, 10);
}

window.addEventListener("load", () => {
  const container = document.getElementById("weightContainer");
  if (container && !container.querySelector(".weight-column")) addWeightColumn();
  const initial = Array.isArray(window.JMPADDY_INITIAL_WEIGHTS) ? window.JMPADDY_INITIAL_WEIGHTS : [];
  restoringWeights = true;
  initial.forEach(weight => {
    const number = Number(weight.number);
    const value = Number(weight.weight);
    if (number < 1 || value <= 0) return;
    while (columnCount * 10 < number) addWeightColumn();
    const input = document.querySelector('.weight-input[data-number="' + number + '"]');
    if (input) input.value = value;
  });
  restoringWeights = false;
  document.querySelectorAll(".weight-column").forEach(calculateColumnTotal);
  calculateBill();
});

document.addEventListener("input", event => {
  if (event.target.matches(".weight-input")) weightChanged(event.target);
  else if (event.target.matches("#price, #paid_amount")) calculateBill();
});
document.addEventListener("change", event => {
  if (event.target.matches("#district")) updateRate();
  if (event.target.matches("#paddy_type")) suggestPaddyPrice();
  if (event.target.matches("#price, #paid_amount")) calculateBill();
});


// Keep the live totals and saved weight payload synchronized even when users
// paste values, use mobile keyboards, or click Recalculate.
function collectWeightEntries() {
  const weights = [];
  document.querySelectorAll(".weight-input").forEach(input => {
    const value = Number(String(input.value || "").trim());
    const number = Number(input.dataset.number);
    if (Number.isFinite(value) && value > 0 && Number.isInteger(number) && number > 0) {
      weights.push({number, weight: value});
    }
  });
  const field = document.getElementById("weightsData");
  if (field) field.value = JSON.stringify(weights);
  return weights;
}
window.collectWeightEntries = collectWeightEntries;

// Subtle pointer-following highlight; disabled for touch-only devices.
if (window.matchMedia && window.matchMedia("(pointer: fine)").matches) {
  document.addEventListener("pointermove", event => {
    document.documentElement.style.setProperty("--pointer-x", event.clientX + "px");
    document.documentElement.style.setProperty("--pointer-y", event.clientY + "px");
  }, {passive: true});
}
