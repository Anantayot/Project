<?php
$host = 'localhost';
$dbname = 'mycommiss';
$username = 'root';
$password = 'note071245';

try {
  $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("❌ Database connection failed: " . $e->getMessage());
}
?>
