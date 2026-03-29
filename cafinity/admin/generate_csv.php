<?php
session_start();
require "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get filters
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$quick_filter = $_POST['quick_filter'] ?? '';

if ($quick_filter) {
    $today = date('Y-m-d');
    if ($quick_filter === 'today') $start_date=$end_date=$today;
    if ($quick_filter === 'this_week') { $start_date=date('Y-m-d',strtotime('monday this week')); $end_date=date('Y-m-d',strtotime('sunday this week')); }
    if ($quick_filter === 'this_month') { $start_date=date('Y-m-01'); $end_date=date('Y-m-t'); }
}

// Fetch orders
$whereClause = "o.status='Approved'";
$params=[];
if($start_date && $end_date){ $whereClause.=" AND DATE(o.created_at) BETWEEN ? AND ?"; $params=[$start_date,$end_date]; }

$sql = "SELECT o.id AS order_id, u.username AS customer_name, c.name AS coffee_name, c.price, o.quantity, o.created_at
        FROM orders o
        JOIN users u ON o.user_id=u.id
        JOIN coffee c ON o.coffee_id=c.id
        WHERE $whereClause
        ORDER BY o.created_at DESC";
$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$orders=$stmt->fetchAll(PDO::FETCH_ASSOC);

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sales_report.csv');
$output = fopen('php://output','w');

// Column titles
fputcsv($output, ['Order ID','Customer','Coffee','Quantity','Price (₱)','Total (₱)','Ordered At']);

// Data
foreach($orders as $o){
    fputcsv($output, [
        $o['order_id'],
        $o['customer_name'],
        $o['coffee_name'],
        $o['quantity'],
        number_format($o['price'],2),
        number_format($o['price']*$o['quantity'],2),
        $o['created_at']
    ]);
}
fclose($output);
exit;
