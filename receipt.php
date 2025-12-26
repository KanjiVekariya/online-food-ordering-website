<?php
session_start();
require('fpdf/fpdf.php');

// ------------------------- CONFIG & DB -------------------------
if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'food_app');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------------------- FETCH DATA ---------------------------

// Fetch latest order
$orderQuery = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_id DESC LIMIT 1");
$orderQuery->bind_param("i", $user_id);
$orderQuery->execute();
$order = $orderQuery->get_result()->fetch_assoc();
if (!$order) die("No orders found.");

// User info
$userQuery = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

// Address
$addressQuery = $conn->prepare("SELECT label, full_address, city, postal_code FROM addresses WHERE address_id = ?");
$addressQuery->bind_param("i", $order['address_id']);
$addressQuery->execute();
$address = $addressQuery->get_result()->fetch_assoc();

// Order items
$itemQuery = $conn->prepare("SELECT oi.quantity, oi.price, mi.name
FROM order_items oi
JOIN menu_items mi ON oi.item_id = mi.item_id
WHERE oi.order_id = ?");
$itemQuery->bind_param("i", $order['order_id']);
$itemQuery->execute();
$items = $itemQuery->get_result();

// Subtotal calculation
$subtotal = 0;
while ($item = $items->fetch_assoc()) {
    $subtotal += $item['price'] * $item['quantity'];
}
$items->data_seek(0); // Reset pointer

$delivery_fee = 96.00;
$gst = round($subtotal * 0.18, 2);
$grand_total = $subtotal + $delivery_fee + $gst;

// -------------------- PDF DESIGN START --------------------

$pdf = new FPDF();
$pdf->AddPage();

// ✅ Load Unicode Font
$pdf->AddFont('DejaVuSans', '', 'DejaVuSans.php', 'fpdf/font/');
$pdf->SetFont('DejaVuSans', '', 12);
$pdf->SetAutoPageBreak(true, 20);

// Logo
$logoPath = 'logo.jpg';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, ($pdf->GetPageWidth() - 60) / 2, 5, 60);
}
$pdf->Ln(35);

// Company Info
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'DailyBites', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 5, 'Delicious Food with authentic Taste', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'support@dailybites.com | +91 64978 56398', 0, 1, 'C');
$pdf->Ln(10);

// Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 6, 'ISSUED TO:', 0, 0);
$pdf->Cell(0, 6, 'INVOICE NO: ' . $order['order_id'], 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 6, $user['name'], 0, 0);
$pdf->Cell(0, 6, 'DATE: ' . date("d.m.Y"), 0, 1);

$pdf->Cell(100, 6, $user['email'], 0, 0);
$pdf->Cell(0, 6, 'DUE DATE: ' . date("d.m.Y", strtotime("+1 day")), 0, 1);

$pdf->MultiCell(100, 6, $address['full_address'] . ", " . $address['city'] . " - " . $address['postal_code'], 0, 'L');
$pdf->Ln(8);

// Table header
$pdf->SetFont('DejaVuSans', '', 10);
$pdf->SetFillColor(237, 230, 227);
$pdf->Cell(90, 10, 'DESCRIPTION', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'UNIT PRICE ($)', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'QTY', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'TOTAL (INR)', 1, 1, 'C', true);

// Table rows
while ($item = $items->fetch_assoc()) {
    $total = $item['price'] * $item['quantity'];
    $pdf->Cell(90, 10, $item['name'], 1);
    $pdf->Cell(30, 10, number_format($item['price'], 2), 1, 0, 'C');
    $pdf->Cell(20, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($total, 2), 1, 1, 'R');
}

// Totals
$pdf->Ln(2);
$pdf->Cell(140, 8, 'SUBTOTAL', 0, 0, 'R');
$pdf->Cell(30, 8,  number_format($subtotal, 2), 0, 1, 'R');

$pdf->Cell(140, 8, 'Delivery Fee', 0, 0, 'R');
$pdf->Cell(30, 8, number_format($delivery_fee, 2), 0, 1, 'R');

$pdf->Cell(140, 8, 'GST (18%)', 0, 0, 'R');
$pdf->Cell(30, 8, number_format($gst, 2), 0, 1, 'R');

$pdf->SetFont('DejaVuSans', '', 12);
$pdf->SetFillColor(237, 230, 227);
$pdf->Cell(140, 10, 'TOTAL', 0, 0, 'R');
$pdf->Cell(30, 10, number_format($grand_total, 2)."(INR)" , 0, 1, 'R', true);

$pdf->Ln(10);

// Bank details
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'BANK DETAILS', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'HDFC Bank', 0, 1);
$pdf->Cell(0, 6, 'Account Name: Avery Davis', 0, 1);
$pdf->Cell(0, 6, 'Account No.: 0123 4567 8901', 0, 1);
$pdf->Cell(0, 6, 'IFSC Code : HDFC0002431', 0, 1);
$pdf->Cell(0, 6, 'GST No. : 12AAECR2971C1Z', 0, 1);

$pdf->Ln(5);

// Stamp image
$stampPath = 'assets/stamp.png';
$stampX = 150;
$stampY = $pdf->GetY();
$stampWidth = 32;

if (file_exists($stampPath)) {
    $pdf->Image($stampPath, $stampX, $stampY, $stampWidth);
    $pdf->Ln(35);
}

// Thank you
$pdf->SetX($stampX);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($stampWidth, 6, 'THANK YOU', 0, 1, 'C');

$pdf->SetX($stampX);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell($stampWidth, 6, 'DailyBites', 0, 1, 'C');

$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 6, 'This is a computer-generated invoice and does not require a physical signature.', 0, 1, 'C');
$pdf->Ln(2);

// Output PDF
ob_clean(); // Clear any prior output
$pdf->Output('D', 'Invoice_' . $order['order_id'] . '.pdf');
exit;
