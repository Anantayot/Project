<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 🌟 กำหนด BASE_URL เพื่อให้ง่ายต่อการเรียกใช้รูปภาพและไฟล์
$BASE_URL = '/user/'; 

// ✅ ต้องแก้ Path ถอยหลังให้ถูกเพื่อหาไฟล์เชื่อมต่อ
include __DIR__ . "../../includes/connectdb.php";

// ✅ ต้องเข้าสู่ระบบก่อน
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

if (!isset($_GET['id'])) {
    die("<p class='text-center mt-5 text-danger'>❌ ไม่พบรหัสคำสั่งซื้อ</p>");
}

$order_id = intval($_GET['id']);

// ดึงข้อมูลคำสั่งซื้อ
$stmt = $conn->prepare("SELECT o.*, c.name 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.customer_id 
                        WHERE o.order_id = ? AND o.customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("<p class='text-center mt-5 text-danger'>❌ ไม่พบคำสั่งซื้อนี้ หรือคุณไม่มีสิทธิ์ดู</p>");
}

// เช็คว่าถ้าชำระเงินไปแล้ว หรือถูกยกเลิก ไม่ควรให้แจ้งซ้ำ
if ($order['payment_status'] === 'ชำระเงินแล้ว' || $order['order_status'] === 'ยกเลิก') {
    echo "<script>alert('ออเดอร์นี้ไม่สามารถแจ้งชำระเงินได้ในขณะนี้'); window.location='../order_detail.php?id=$order_id';</script>";
    exit;
}

/* --- ฟังก์ชัน PromptPay Payload (คงเดิม) --- */
function generatePromptPayPayload($promptPayID, $amount = 0.00) {
    $id = preg_replace('/[^0-9]/', '', $promptPayID);
    if (strlen($id) == 10) $id = '0066' . substr($id, 1);
    $data = [
        '00' => '01', '01' => '11',
        '29' => formatField('00', 'A000000677010111') . formatField('01', $id),
        '53' => '764', '54' => sprintf('%0.2f', $amount), '58' => 'TH',
    ];
    $payload = '';
    foreach ($data as $tag => $val) $payload .= $tag . sprintf('%02d', strlen($val)) . $val;
    $payload .= '6304';
    return $payload . strtoupper(crc16($payload));
}
function formatField($id, $value) { return $id . sprintf('%02d', strlen($value)) . $value; }
function crc16($data) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= ord($data[$i]) << 8;
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) $crc = ($crc << 1) ^ 0x1021;
            else $crc <<= 1;
            $crc &= 0xFFFF;
        }
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

/* ✅ ยืนยันการชำระเงิน */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 🌟 Path ถอยหลัง 3 ขั้น: payment -> order -> user -> เข้า admin/uploads/slips/
    $uploadDir = __DIR__ . "/../../../admin/uploads/slips/";

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $fileName = "";
    if (isset($_FILES['slip']) && $_FILES['slip']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($ext, $allowed)) {
            die("<script>alert('❌ กรุณาอัปโหลดเฉพาะไฟล์รูปภาพเท่านั้น'); window.history.back();</script>");
        }

        $fileName = "slip_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        if (!move_uploaded_file($_FILES['slip']['tmp_name'], $uploadDir . $fileName)) {
            die("❌ อัปโหลดไฟล์ล้มเหลว");
        }
    }

    // อัปเดตสถานะ DB
    $stmt = $conn->prepare("UPDATE orders SET 
                            payment_status = 'รอดำเนินการ',
                            admin_verified = 'กำลังตรวจสอบ',
                            slip_image = :slip,
                            payment_date = NOW()
                            WHERE order_id = :oid AND customer_id = :cid");
    $stmt->execute([':slip' => $fileName, ':oid' => $order_id, ':cid' => $customer_id]);

    // Webhook (คงเดิม)
    $webhook_url = "http://103.40.119.91:5678/webhook/778284f3-0ba4-473f-9d10-fee5d2416f4f";
    $payload_data = [
        'order_id' => $order_id,
        'customer_id' => $customer_id,
        'customer_name' => $order['name'],
        'amount' => $order['total_price'],
        'slip_image' => $fileName,
        'status' => 'payment_submitted',
        'timestamp' => date('Y-m-d H:i:s')
    ];

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

    echo "<script>alert('✅ แจ้งชำระเงินเรียบร้อยแล้ว!'); window.location='../order_detail.php?id=$order_id';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งชำระเงิน | MyCommiss</title>
    <link rel="icon" type="image/png" href="../../../includes/icon_mycommiss.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        /* CSS ส่วนใหญ่คงเดิม */
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');
        body { background-color: #f8f9fa; font-family: "Prompt", sans-serif; color: #333; }
        .payment-wrapper { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 15px; }
        .card-payment { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: #fff; width: 100%; max-width: 550px; overflow: hidden; }
        .card-header-payment { background-color: #D10024; color: #fff; padding: 20px; text-align: center; font-size: 1.25rem; font-weight: 700; }
        .qr-box { background: #fff; padding: 20px; border-radius: 15px; border: 2px dashed #D10024; display: inline-block; margin: 15px 0; }
        .total-price { font-size: 2.2rem; font-weight: 700; color: #D10024; margin-bottom: 0; }
        .form-control { border-radius: 10px; padding: 12px 15px; border: 1px solid #ddd; }
        .btn-submit { background-color: #D10024; color: #fff; border-radius: 50px; font-weight: 600; padding: 12px; border: none; transition: 0.3s; }
        .btn-submit:hover { background-color: #a5001b; transform: translateY(-2px); }
        .btn-outline-custom { border: 1px solid #ddd; color: #555; border-radius: 50px; padding: 10px; text-decoration: none; display: block; text-align: center; transition: 0.3s; }
        .btn-outline-custom:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>

<?php include __DIR__ . "../../includes/navbar_user.php"; ?>

<div class="payment-wrapper">
    <div class="card-payment">
        <div class="card-header-payment">
            <i class="bi bi-qr-code-scan me-2"></i>แจ้งชำระเงิน
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <p class="text-muted mb-1">คำสั่งซื้อหมายเลข</p>
                <h4 class="fw-bold text-dark">#<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></h4>
            </div>

            <?php if ($order['payment_method'] === 'QR'): ?>
                <?php
                    $shopPromptPay = "0903262100";
                    $payload = generatePromptPayPayload($shopPromptPay, $order['total_price']);
                ?>
                <div class="text-center bg-light rounded-4 p-4 mb-4 border">
                    <p class="fw-semibold text-secondary mb-3">สแกน QR เพื่อชำระเงิน</p>
                    <div class="qr-box shadow-sm">
                        <div id="qrcode"></div>
                    </div>
                    <div class="mt-3">
                        <div class="text-muted small">ยอดที่ต้องชำระ</div>
                        <div class="total-price"><?= number_format($order['total_price'], 2) ?> ฿</div>
                    </div>
                </div>
                <script>
                    new QRCode(document.getElementById("qrcode"), {
                        text: "<?= $payload ?>",
                        width: 180, height: 180,
                        colorDark : "#000000", colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                </script>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">แนบสลิปการโอนเงิน <span class="text-danger">*</span></label>
                    <input type="file" name="slip" class="form-control" accept="image/*" required>
                </div>
                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-submit fs-5">ยืนยันการชำระเงิน</button>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="../orders.php" class="btn-outline-custom"><i class="bi bi-clock-history"></i> ประวัติ</a>
                        </div>
                        <div class="col-6">
                            <a href="../order_detail.php?id=<?= $order_id ?>" class="btn-outline-custom">รายละเอียด</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="py-4 border-top text-center bg-white">
    © <?= date('Y') ?> MyCommiss | ระบบร้านค้าออนไลน์คอมพิวเตอร์
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>