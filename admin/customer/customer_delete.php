<?php
include __DIR__ . "/../partials/connectdb.php";

$id = $_GET['id'] ?? null;
if (!$id) die("ไม่พบข้อมูลลูกค้า");

$stmt = $conn->prepare("DELETE FROM customers WHERE customer_id=?");
$stmt->execute([$id]);
header("Location: customers.php");
exit;
?>
