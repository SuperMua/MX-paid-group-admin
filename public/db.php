<?php
// 引入数据库配置文件
require '../config/config.php';
//引入创建订单求文件
require_once '../pay/lib/pay.php';

// 创建数据库连接
$conn = new mysqli($host, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 获取表单数据
$name = $_POST['name'];
$order_number = $_POST['WIDout_trade_no'];
$ip_address = $_POST['ip_address'];
$ip_location = $_POST['ip_location'];
$money = $_POST['money'];
$payment_method = $_POST['payment_method'];
$payment_time = $_POST['payment_time'];
$payment_status = '未支付'; // 默认支付状态

// 插入数据到数据库
$sql = "INSERT INTO orders (name, order_number, ip_address, ip_location, money, payment_method, payment_time, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $name, $order_number, $ip_address, $ip_location, $money, $payment_method, $payment_time, $payment_status);

if ($stmt->execute()) {
    echo "";//订单提交成功！
} else {
    echo "订单提交失败：" . $conn->error;
}

// 关闭连接
$stmt->close();
$conn->close();

?>
