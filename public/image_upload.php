<?php
// 启动会话
session_start();
// 连接数据库
require __DIR__ . '/../config/config.php';

// 获取客户端 IP 地址
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
    return $ipaddress;
}

$ipAddress = get_client_ip();

// 查询当前 IP 地址的所有图片的审核状态
$stmt = $conn->prepare("SELECT status FROM images WHERE ip_address =?");
$stmt->bind_param("s", $ipAddress);
$stmt->execute();
$result = $stmt->get_result();

$allApproved = true; // 假设所有图片都已审核通过
$allRejected = false; // 假设没有图片被全部拒绝
$anyPending = false; // 假设没有图片处于待审核状态

if ($result->num_rows > 0) {
    $hasApproved = false;
    $hasRejected = false;
    $hasPending = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'approved') {
            $hasApproved = true;
        }
        if ($row['status'] == 'rejected') {
            $hasRejected = true;
        }
        if ($row['status'] == 'pending') {
            $hasPending = true;
        }
    }
    // 只要有一张图片被拒绝，就显示全部不通过
    if ($hasRejected) {
	    $allRejected = true;
	    $allApproved = false;
	 }
    // 只要有一张图片处于待审核，就显示审核中
    if ($hasPending) {
        $anyPending = true;
    }
}

// 查询当前 IP 地址的最新上传记录
$stmtLatest = $conn->prepare("SELECT upload_time, status FROM images WHERE ip_address =? ORDER BY upload_time DESC LIMIT 1");
$stmtLatest->bind_param("s", $ipAddress);
$stmtLatest->execute();
$resultLatest = $stmtLatest->get_result();

$latestUploadTime = '';
$latestStatus = '';

if ($resultLatest->num_rows > 0) {
    $row = $resultLatest->fetch_assoc();
    $latestUploadTime = $row['upload_time'];
    $latestStatus = $row['status'];
}

// 查询当前 IP 地址的上传图片总数
$stmtTotal = $conn->prepare("SELECT COUNT(*) as total_uploads FROM images WHERE ip_address =?");
$stmtTotal->bind_param("s", $ipAddress);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$totalUploads = 0;
if ($resultTotal->num_rows > 0) {
    $rowTotal = $resultTotal->fetch_assoc();
    $totalUploads = $rowTotal['total_uploads'];
}

// 查询当前 IP 地址是否有上传记录
$stmt = $conn->prepare("SELECT COUNT(*) as upload_count FROM images WHERE ip_address =?");
$stmt->bind_param("s", $ipAddress);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $uploadCount = $row['upload_count'];
    if ($uploadCount > 0) {
        $hasUpload = true;
    } else {
        $hasUpload = false;
    }
} else {
    $hasUpload = false;
}

// 查询自动审核开关状态
$stmtAutoAudit = $conn->prepare("SELECT is_auto_audit_enabled FROM auto_settings LIMIT 1");
$stmtAutoAudit->execute();
$resultAutoAudit = $stmtAutoAudit->get_result();
$isAutoAuditEnabled = 0;
if ($resultAutoAudit->num_rows > 0) {
    $rowAutoAudit = $resultAutoAudit->fetch_assoc();
    $isAutoAuditEnabled = $rowAutoAudit['is_auto_audit_enabled'];
}

// 如果自动审核开关开启，更新所有待审核的图片状态为“通过审核”
if ($isAutoAuditEnabled == 1) {
    $stmtUpdate = $conn->prepare("UPDATE images SET status = 'approved' WHERE status = 'pending'");
    $stmtUpdate->execute();
}

// 生成令牌
if ($allApproved && !$anyPending) {
    $_SESSION['image_token'] = bin2hex(random_bytes(32));
}
?>