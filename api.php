<?php
// ชื่อไฟล์เก็บข้อมูล
$filename = 'process.txt';

// Config Header
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// สร้างไฟล์เริ่มต้นถ้ายังไม่มี
if (!file_exists($filename)) {
    file_put_contents($filename, "lock,0");
}

// รับค่า
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$action = $input['action'] ?? $_GET['action'] ?? '';
$minutes = $input['minutes'] ?? 0;

// ฟังก์ชันเขียนไฟล์
function writeStatusFile($file, $content) {
    @file_put_contents($file, $content);
}

// --- LOGIC ---

if ($action == 'tick') {
    // 1. อ่านค่าเดิม
    $content = file_get_contents($filename);
    $parts = explode(",", $content);
    $status = trim($parts[0]);
    $currentMinutes = intval($parts[1] ?? 0);

    // 2. ถ้ายังเล่นได้ (unlock) และมีเวลาเหลือ -> ลดเวลาลง 1 นาที
    if ($status == 'unlock' && $currentMinutes > 0) {
        $currentMinutes = $currentMinutes - 1;
        // ถ้าเวลาหมดพอดี ให้เปลี่ยนสถานะเป็น lock เลย
        if ($currentMinutes <= 0) {
            $currentMinutes = 0;
            $status = 'lock';
        }
        writeStatusFile($filename, "$status,$currentMinutes");
    }

    // 3. ส่งค่ากลับ
    echo json_encode([
        "status" => "ok",
        "is_locked" => ($status == 'lock'),
        "time_remaining" => $currentMinutes
    ]);
} 
elseif ($action == 'set_time') {
    $newData = "unlock," . $minutes;
    writeStatusFile($filename, $newData);
    echo json_encode(["status" => "ok", "message" => "Set to $minutes mins"]);
}
elseif ($action == 'add_time') {
    $content = file_get_contents($filename);
    $parts = explode(",", $content);
    $current = intval($parts[1] ?? 0);
    $newTime = $current + $minutes;
    writeStatusFile($filename, "unlock," . $newTime);
    echo json_encode(["status" => "ok", "message" => "Added time"]);
}
elseif ($action == 'lock') {
    writeStatusFile($filename, "lock,0");
    echo json_encode(["status" => "ok", "message" => "Locked"]);
}
else {
    // get_status หรืออื่นๆ
    $content = file_get_contents($filename);
    $parts = explode(",", $content);
    echo json_encode([
        "status" => "ok",
        "is_locked" => (trim($parts[0]) == 'lock'),
        "time_remaining" => intval($parts[1] ?? 0)
    ]);
}
?>