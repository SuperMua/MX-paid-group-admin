<?php
// 开启详细的错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 数据库连接信息
require __DIR__ . '/../config/config.php';

// 处理 POST 请求（更新状态）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['status'])) {
        $newStatus = (int)$_POST['status'];
        $sql = "UPDATE auto_settings SET is_auto_audit_enabled =? WHERE id = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $newStatus);
        if ($stmt->execute()) {
            $response = ['success' => true, 'is_auto_audit_enabled' => $newStatus];
        } else {
            $response = ['success' => false, 'message' => '更新状态失败: '. $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => '缺少状态参数'];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// 处理 GET 请求（获取状态）
$sql = "SELECT is_auto_audit_enabled FROM auto_settings LIMIT 1";
$result = $conn->query($sql);

if ($result === false) {
    // 查询失败，输出错误信息
    echo "查询失败: ". $conn->error;
    // 可以根据需要进行其他处理，比如返回默认状态
    $isAutoAuditEnabled = 0;
} else {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $isAutoAuditEnabled = $row['is_auto_audit_enabled'];
    } else {
        $isAutoAuditEnabled = 0;
    }
    // 释放查询结果资源
    $result->free();
}
?>