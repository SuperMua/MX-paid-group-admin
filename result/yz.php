<?php
//yz.php验证ip
// 引入数据库配置文件
require '../config/config.php';

// 获取URL参数中的订单ID和当前IP地址
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$userIP = $_SERVER['REMOTE_ADDR'];

// 查询指定IP和ID对应的订单支付状态
$sql = "SELECT payment_status FROM orders WHERE id = ? AND ip_address = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("查询准备失败: " . $conn->error);
}

$stmt->bind_param("is", $orderId, $userIP);
$stmt->execute();
$result = $stmt->get_result();

// 检查查询结果
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $paymentStatus = $row['payment_status'];

    if ($paymentStatus == '已支付') {
        // 已支付，停留在当前页面
        echo "";
    } else {
        // 未支付，跳转到首页
        header("Location: error.html");
        exit;
    }
} else {
    // 订单不存在或查询失败，跳转到首页
    header("Location: error.html");
    exit;
}

// 关闭语句和数据库连接
$stmt->close();
$conn->close();
?>