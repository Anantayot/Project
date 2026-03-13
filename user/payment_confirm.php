<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("connectdb.php");

// ✅ ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit;
}

$customer_id = $_SESSION['customer_id'];

// ✅ ตรวจสอบว่ามี id คำสั่งซื้อหรือไม่
if (!isset($_GET['id'])) {
  die("<p class='text-center mt-5 text-danger'>❌ ไม่พบรหัสคำสั่งซื้อ</p>");
}

$order_id = intval($_GET['id']);

// ✅ ดึงข้อมูลคำสั่งซื้อของลูกค้าคนนั้น
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND customer_id = ?");
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
   ✅ ยืนยันการชำระเงิน (อัปโหลดสลิป + อัปเดตสถานะ)
   ======================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $uploadDir = dirname(__DIR__) . "/admin/uploads/slips/";

  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
  if (!is_writable($uploadDir)) die("<p class='text-danger text-center mt-5'>❌ ไม่มีสิทธิ์เขียนไฟล์ใน: $uploadDir</p>");

  $fileName = "";
  if (!empty($_FILES['slip']['name'])) {
    $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
    $fileName = "slip_" . time() . "_" . rand(1000,9999) . "." . $ext;
    $targetFile = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['slip']['tmp_name'], $targetFile)) {
      die("<p class='text-danger text-center mt-5'>❌ ไม่สามารถอัปโหลดไฟล์ได้</p>");
    }
  }

  // --- เริ่มต้นส่วนที่ต้องแทนที่ ---
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

  $payload_data = [
      'order_id'      => $order_id,
      'customer_id'   => $customer_id,
      'amount'        => $order['total_price'],
      'slip_image'    => $fileName,
      'status'        => 'payment_submitted',
      'timestamp'     => date('Y-m-d H:i:s')
  ];

  $ch = curl_init($webhook_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5); 

  // ยิง Webhook (ไม่เช็ค error เพื่อให้หน้าเว็บทำงานต่อได้ลื่นไหล)
  curl_exec($ch);
  curl_close($ch);

  echo "<script>
    alert('✅ แจ้งชำระเงินเรียบร้อยแล้ว! ระบบจะทำการตรวจสอบโดยแอดมิน');
    window.location='order_detail.php?id=$order_id';
  </script>";
  exit;
  // --- สิ้นสุดส่วนที่ต้องแทนที่ ---
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แจ้งชำระเงิน | MyCommiss</title>
  <link rel="icon" type="image/png" href="icon_mycommiss.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <style>
    body { background-color: #fff; font-family: "Prompt", sans-serif; }
    :root { --red: #D10024; }

    .card-header {
      background: var(--red);
      color: #fff;
      font-weight: 600;
      text-align: center;
    }

    .btn-primary, .btn-outline-primary:hover {
      background-color: var(--red);
      border-color: var(--red);
      color: #fff;
    }

    .btn-outline-primary {
      border-color: var(--red);
      color: var(--red);
    }

    .btn-success {
      background-color: #28a745;
      border: none;
    }

    #qrcode {
      background: white;
      padding: 10px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    footer {
      background-color: var(--red);
      color: #fff;
      margin-top: 50px;
      padding: 15px;
      text-align: center;
    }

    label {
      font-weight: 500;
    }
  </style>
</head>
<body>

<?php include("navbar_user.php"); ?>

<div class="container mt-4">
  <div class="card shadow-lg border-0 mx-auto" style="max-width:600px;">
    <div class="card-header">💰 แจ้งชำระเงินคำสั่งซื้อ #<?= $order_id ?></div>
    <div class="card-body text-center">
      <p><strong>วิธีชำระ:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>

      <?php if ($order['payment_method'] === 'QR'): ?>
        <?php
          $shopPromptPay = "0903262100"; // หมายเลขพร้อมเพย์ร้าน
          $payload = generatePromptPayPayload($shopPromptPay, $order['total_price']);
        ?>
        <div class="text-center my-4">
          <h5 class="fw-bold" style="color:#D10024;">📱 สแกน QR พร้อมเพย์ เพื่อชำระเงิน</h5>
          <div id="qrcode" class="d-inline-block"></div>
          <p class="mt-3 text-muted">
            💵 ยอดชำระ <span class="fw-semibold text-danger"><?= number_format($order['total_price'], 2) ?></span> บาท<br>
            ☎️ พร้อมเพย์: <?= htmlspecialchars($shopPromptPay) ?>
          </p>
        </div>

        <script>
          const payload = "<?= $payload ?>";
          new QRCode(document.getElementById("qrcode"), {
            text: payload,
            width: 200,
            height: 200
          });
        </script>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="mt-4 text-start">
        <div class="mb-3">
          <label for="slip" class="form-label">แนบสลิปการชำระเงิน</label>
          <input type="file" name="slip" id="slip" class="form-control" accept="image/*" required>
        </div>

        <div class="d-grid gap-2 mt-4">
          <button type="submit" class="btn btn-primary">✅ ยืนยันการชำระเงิน</button>
          <a href="orders.php" class="btn btn-secondary">⬅️ กลับหน้าคำสั่งซื้อ</a>
          <a href="order_detail.php?id=<?= $order_id ?>" class="btn btn-outline-primary">🔍 ดูรายละเอียดคำสั่งซื้อ</a>
        </div>
      </form>
    </div>
  </div>
</div>

<footer>
  © <?= date('Y') ?> MyCommiss | แจ้งชำระเงิน
</footer>

</body>

</html>

