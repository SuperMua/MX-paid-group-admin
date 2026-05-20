<?php
// 引入数据库配置文件
require __DIR__ . '/../config/config.php';

// 获取用户当前IP地址
$userIP = $_SERVER['REMOTE_ADDR'];

// 查询与用户IP地址对应的最新未支付订单
$sql = "SELECT id FROM orders WHERE ip_address = ? AND payment_status = '未支付' ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("查询准备失败: " . $conn->error);
}

$stmt->bind_param("s", $userIP);
$stmt->execute();
$result = $stmt->get_result();

// 检查是否有匹配的订单
if ($result->num_rows > 0) {
    // 获取订单ID
    $order = $result->fetch_assoc();
    $orderId = $order['id'];

    // 关闭查询语句
    $stmt->close();

    // 准备更新订单状态的SQL语句
    $updateSql = "UPDATE orders SET payment_status = '已支付' WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);

    if ($updateStmt === false) {
        die("更新失败: " . $conn->error);
    }

    $updateStmt->bind_param("i", $orderId);
    $updateResult = $updateStmt->execute();

    if ($updateResult) {
        // 更新成功，跳转到result.php页面，并传递订单ID
        $redirectUrl = "../result/result.php?status=success&order_id=" . $orderId;
        header("Location: $redirectUrl");
        exit;
    } else {
        // 更新失败，跳转到result.php页面并显示错误信息
        $redirectUrl = "../result/result.php?status=error&error=" . urlencode($conn->error);
        header("Location: $redirectUrl");
        exit;
    }

    $updateStmt->close();
} else {
    // 没有找到匹配的订单，跳转到result.php页面并显示错误信息
    $redirectUrl = "../result/result.php?status=error&error=" . urlencode("没有找到匹配的订单");
    header("Location: $redirectUrl");
    exit;
}

// 关闭数据库连接
$conn->close();
?>