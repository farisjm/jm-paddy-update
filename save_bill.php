<?php
require "db.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: bill.php");
    exit;
}
function field($key) { return trim((string)($_POST[$key] ?? "")); }
$bill_no = field("bill_no");
$bill_date = field("bill_date");
$farmer_name = field("farmer_name");
$phone = field("phone");
$address = field("address");
$district = field("district");
$paddy_type = field("paddy_type");
$weights = json_decode((string)($_POST["weights"] ?? "[]"), true);
$validDistricts = ["Vavuniya" => 72, "Mannar" => 73, "Mullaitivu" => 73, "Kilinochchi" => 73, "Jaffna" => 73];
if ($bill_no === "" || $farmer_name === "" || $bill_date === "" || !isset($validDistricts[$district]) || $paddy_type === "" || !is_array($weights)) {
    http_response_code(422);
    exit("Please fill all required fields and choose a valid district. <a href='bill.php'>Return to form</a>");
}
$district_rate = (float)$validDistricts[$district];
$price = filter_var($_POST["price"] ?? null, FILTER_VALIDATE_FLOAT);
if ($price === false || $price < 0) {
    http_response_code(422);
    exit("Please enter a valid paddy price. <a href='bill.php'>Return to form</a>");
}
$total_weight = 0.0;
$cleanWeights = [];
foreach ($weights as $item) {
    if (!isset($item["number"], $item["weight"]) || !is_numeric($item["weight"])) continue;
    $number = (int)$item["number"];
    $weight = (float)$item["weight"];
    if ($number < 1 || $weight <= 0) continue;
    $total_weight += $weight;
    $cleanWeights[] = ["number" => $number, "weight" => $weight];
}
if ($total_weight <= 0) {
    http_response_code(422);
    exit("Enter at least one weight greater than zero. <a href='bill.php'>Return to form</a>");
}
$net_weight = $total_weight / $district_rate;
$total_amount = $net_weight * (float)$price;
try {
    $conn->begin_transaction();
    $stmt = $conn->prepare("INSERT INTO farmers (name, phone, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $farmer_name, $phone, $address);
    $stmt->execute();
    $farmer_id = $conn->insert_id;
    $stmt = $conn->prepare("INSERT INTO bills (bill_no, farmer_id, bill_date, district, district_rate, paddy_type, total_weight, net_weight, price, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissdsdddd", $bill_no, $farmer_id, $bill_date, $district, $district_rate, $paddy_type, $total_weight, $net_weight, $price, $total_amount);
    $stmt->execute();
    $bill_id = $conn->insert_id;
    $weightStmt = $conn->prepare("INSERT INTO bill_weights (bill_id, weight_no, weight) VALUES (?, ?, ?)");
    foreach ($cleanWeights as $item) {
        $weight_no = $item["number"];
        $weight = $item["weight"];
        $weightStmt->bind_param("iid", $bill_id, $weight_no, $weight);
        $weightStmt->execute();
    }
    $conn->commit();
    header("Location: view_bill.php?id=" . $bill_id . "&saved=1");
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo "<!doctype html><html lang='en'><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Save failed</title><body style='font:16px Arial;padding:30px;background:#f4fbf5'><h2 style='color:#174b30'>Unable to save bill</h2><p>Check that the database tables are configured and that the bill number is not already in use.</p><p><a href='bill.php'>Return to bill form</a></p></body></html>";
}
?>