<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("../../includes/connectdb.php");

// ✅ ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: ../../login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ตรวจสอบว่ามี id คำสั่งซื้อหรือไม่
if (!isset($_GET['id'])) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบรหัสคำสั่งซื้อ</p>");
}

$order_id = intval($_GET['id']);

// ✅ ดึงข้อมูลคำสั่งซื้อพร้อมชื่อและอีเมลลูกค้า
$stmt = $conn->prepare("SELECT o.*, c.name, c.email 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.customer_id 
                        WHERE o.order_id = ? AND o.customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบคำสั่งซื้อนี้ หรือคุณไม่มีสิทธิ์ดู</p>");
}

/* =======================================================
   ✅ ฟังก์ชันสร้าง QR พร้อมเพย์ (มาตรฐาน EMVCo)
   ======================================================= */
function generatePromptPayPayload($promptPayID, $amount = 0.00) {
  $id = preg_replace('/[^0-9]/', '', $promptPayID);
  if (strlen($id) == 10) {
    $id = '0066' . substr($id, 1);
  }

  $data = [
    '00' => '01',
    '01' => '11',
    '29' => formatField('00', 'A000000677010111') . formatField('01', $id),
    '53' => '764',
    '54' => sprintf('%0.2f', $amount),
    '58' => 'TH',
  ];

  $payload = '';
  foreach ($data as $id => $val) {
    $payload .= $id . sprintf('%02d', strlen($val)) . $val;
  }
  $payload .= '6304';
  return $payload . strtoupper(crc16($payload));
}

function formatField($id, $value) {
  return $id . sprintf('%02d', strlen($value)) . $value;
}

function crc16($data) {
  $crc = 0xFFFF;
  for ($i = 0; $i < strlen($data); $i++) {
    $crc ^= ord($data[$i]) << 8;
    for ($j = 0; $j < 8; $j++) {
      if ($crc & 0x8000)
        $crc = ($crc << 1) ^ 0x1021;
      else
        $crc <<= 1;
      $crc &= 0xFFFF;
    }
  }
  return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

/* =======================================================
    ✅ ยืนยันการชำระเงิน (อัปโหลดสลิป + อัปเดตสถานะ) มีระบบความปลอดภัย
    ======================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $uploadDir = __DIR__ . "/../../../admin/uploads/slips/";

  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
  if (!is_writable($uploadDir)) die("<script>alert('❌ ระบบ Server ไม่มีสิทธิ์เขียนไฟล์'); window.history.back();</script>");

  $fileName = "";
  
  // 🔒 1. ตรวจสอบว่ามีการอัปโหลดไฟล์มาจริงๆ และไม่มี Error
  if (isset($_FILES['slip']) && $_FILES['slip']['error'] === UPLOAD_ERR_OK) {
      $tmpName = $_FILES['slip']['tmp_name'];
      $fileSize = $_FILES['slip']['size'];
      
      // 🔒 2. ตรวจสอบขนาดไฟล์ (จำกัดไว้ที่ไม่เกิน 5MB)
      $maxSize = 5 * 1024 * 1024; // 5 MB
      if ($fileSize > $maxSize) {
          echo "<script>alert('❌ ไฟล์มีขนาดใหญ่เกินไป (จำกัดไม่เกิน 5MB)'); window.history.back();</script>";
          exit;
      }

      // 🔒 3. ตรวจสอบประเภทไฟล์จริงๆ จากเนื้อหา (MIME Type) ป้องกันการเปลี่ยนนามสกุลไฟล์หลอกๆ
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mimeType = finfo_file($finfo, $tmpName);
      finfo_close($finfo);

      $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!in_array($mimeType, $allowedMimeTypes)) {
          echo "<script>alert('❌ อนุญาตให้อัปโหลดเฉพาะไฟล์รูปภาพเท่านั้น (JPG, PNG, GIF, WEBP)'); window.history.back();</script>";
          exit;
      }

      // 🔒 4. ตรวจสอบนามสกุลไฟล์ (Extension Whitelist)
      $ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
      $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      if (!in_array($ext, $allowedExtensions)) {
          echo "<script>alert('❌ นามสกุลไฟล์ไม่ถูกต้อง'); window.history.back();</script>";
          exit;
      }

      // 🔒 5. สุ่มชื่อไฟล์ใหม่ให้เดาได้ยาก ป้องกันการเกิดชื่อซ้ำและช่องโหว่ LFI
      $fileName = "slip_" . date('YmdHis') . "_" . uniqid() . "." . $ext;
      $targetFile = $uploadDir . $fileName;

      if (!move_uploaded_file($tmpName, $targetFile)) {
          echo "<script>alert('❌ ไม่สามารถบันทึกไฟล์ลงเซิร์ฟเวอร์ได้'); window.history.back();</script>";
          exit;
      }
      
  } else {
      // กรณีไม่แนบสลิปมา หรือเกิด Error ระหว่างอัปโหลด
      echo "<script>alert('❌ กรุณาแนบภาพสลิปที่ถูกต้อง'); window.history.back();</script>";
      exit;
  }

  // ✅ ต้องดึงยอดเงินจาก DB อีกครั้ง เพื่อให้มีค่าส่งไป Webhook
  $stmt_check = $conn->prepare("SELECT total_price FROM orders WHERE order_id = ? AND customer_id = ?");
  $stmt_check->execute([$order_id, $customer_id]);
  $order_data = $stmt_check->fetch(PDO::FETCH_ASSOC);
  $total_amount = $order_data ? $order_data['total_price'] : 0;

  // ✅ อัปเดตสถานะการชำระเงิน
  $stmt = $conn->prepare("UPDATE orders 
                          SET payment_status = 'รอดำเนินการ',
                              admin_verified = 'กำลังตรวจสอบ',
                              slip_image = :slip,
                              payment_date = NOW()
                          WHERE order_id = :oid AND customer_id = :cid");
  $stmt->execute([
      ':slip' => $fileName,
      ':oid' => $order_id,
      ':cid' => $customer_id
  ]);

  /* =======================================================
      ✅ ส่งข้อมูลไปยัง Webhook หลังจากบันทึก DB สำเร็จ
      ======================================================= */
  $webhook_url = "http://103.40.119.91:5678/webhook-test/778284f3-0ba4-473f-9d10-fee5d2416f4f";

  // ✅ เพิ่มรหัสคำสั่งซื้อที่มี # และ email ของลูกค้าเข้าไปใน payload
  $payload_data = [
      'order_id'      => $order_id,
      'order_number'  => '#' . str_pad($order_id, 5, '0', STR_PAD_LEFT), // เพิ่ม #00154
      'customer_id'   => $customer_id,
      'customer_name' => $order['name'],
      'customer_email'=> $order['email'], // เพิ่ม Email ลูกค้า
      'amount'        => $total_amount, 
      'slip_image'    => $fileName,
      'status'        => 'payment_submitted',
      'timestamp'     => date('Y-m-d H:i:s')
  ];

  // ตรวจสอบว่ามีฟังก์ชัน curl_init ไหม
  if (function_exists('curl_init')) {
      $ch = curl_init($webhook_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_data));
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
      curl_exec($ch);
      curl_close($ch);
  }

  echo "<script>
      alert('✅ แจ้งชำระเงินเรียบร้อยแล้ว! ระบบจะทำการตรวจสอบโดยแอดมิน');
      window.location='../order_detail.php?id=$order_id';
  </script>";
  exit;
}
?>