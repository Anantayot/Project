<?php
session_start();

// ✅ ลบข้อมูลทั้งหมดใน session
session_unset();
session_destroy();

// ✅ ตั้งค่า Toast สำหรับแจ้งเตือนหน้า index.php
session_start();
$_SESSION['toast_success'] = "✅ ออกจากระบบสำเร็จ";

header("Location: index.php");
exit;
?>
