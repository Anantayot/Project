<?php
session_start();

// 1. ล้างข้อมูลในตัวแปร Session ทั้งหมด
$_SESSION = array();

// 2. ถ้าต้องการลบคุกกี้ของ Session ในเบราว์เซอร์ด้วย (แนะนำเพื่อความปลอดภัย)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. ทำลาย Session บน Server
session_destroy();

// 4. ส่งกลับไปหน้า Login 
// หากไฟล์นี้อยู่ใน admin/logout.php ให้ตรวจสอบว่า login.php อยู่ในโฟลเดอร์เดียวกันหรือไม่
header("Location: login.php");
exit;
?>