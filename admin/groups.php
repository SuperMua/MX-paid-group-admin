<?php
// groups.php
require_once 'login_check.php';
require_once 'virtual_data_helper.php';

// 引入数据库配置文件
require_once __DIR__ . '/../config/config.php';
//查询adnin
$adminSql = "SELECT * FROM admin";
$adminStmt = $conn->query($adminSql);

if ($adminStmt->num_rows > 0) {
    $adminInfo = $adminStmt->fetch_assoc();
} else {
    die("没有找到管理员信息");
}

if (vd_is_enabled()) {
    $snapshot = vd_get_dashboard_snapshot();
    $translatedOrders = vd_get_orders_all();

    session_start();
    $_SESSION['totalIncome'] = $snapshot['total_income'];
    $_SESSION['todayIncome'] = $snapshot['today_income'];
    $_SESSION['todayOrders'] = $snapshot['today_orders'];
    $_SESSION['orders'] = $translatedOrders;
    $_SESSION['adminInfo'] = $adminInfo;

    $conn->close();
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $scriptDir = $scriptName !== '' ? dirname($scriptName) : '/admin';
    if ($scriptDir === '\\' || $scriptDir === '/' || $scriptDir === '.') {
        $scriptDir = '/admin';
    }
    header('Location: ' . rtrim($scriptDir, '/') . '/index.php');
    exit;
}

// 定义支付方式的映射数组
$paymentMethodMap = [
    'wxpay' => '微信',
    'alipay' => '支付宝'
];

// 查询订单列表
$ordersSql = "SELECT * FROM orders ORDER BY payment_time DESC";
$ordersStmt = $conn->prepare($ordersSql);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();

// 遍历订单结果集并翻译支付方式
$translatedOrders = [];
while ($order = $ordersResult->fetch_assoc()) {
    $order['payment_method'] = $paymentMethodMap[$order['payment_method']] ?? '未知支付方式';
    $translatedOrders[] = $order;
}


// 查询orders表中的总收入（只计算已支付的订单）
$totalIncomeSql = "SELECT SUM(money) AS totalIncome FROM orders WHERE payment_status = '已支付'";

// 查询今日已支付订单的总收入
$todayIncomeSql = "SELECT SUM(money) AS todayIncome FROM orders WHERE payment_status = '已支付' AND payment_time LIKE '%" . date('Y-m-d') . "%'";

$totalIncomeStmt = $conn->query($totalIncomeSql);
$todayIncomeStmt = $conn->query($todayIncomeSql);

$totalIncome = $totalIncomeStmt->fetch_assoc()['totalIncome'] ?? 0;
$todayIncome = $todayIncomeStmt->fetch_assoc()['todayIncome'] ?? 0;

// 查询今日订单数量
$todayOrdersSql = "SELECT COUNT(*) AS todayOrders FROM orders WHERE payment_time LIKE '%" . date('Y-m-d') . "%' AND payment_status = '已支付'";
$todayOrdersStmt = $conn->query($todayOrdersSql);
$todayOrders = $todayOrdersStmt->fetch_assoc()['todayOrders'] ?? 0;

// 查询订单列表
$ordersSql = "SELECT * FROM orders ORDER BY payment_time DESC";
$ordersStmt = $conn->prepare($ordersSql);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();

// 检查数据是否获取成功 — 允许空数据，不阻断页面渲染
$hasData = !($totalIncome === null || $todayIncome === null || $todayOrders === null);
if (!$hasData) {
    $totalIncome = 0;
    $todayIncome = 0;
    $todayOrders = 0;
    $translatedOrders = array();
}

// 存储数据到会话
session_start();
$_SESSION['totalIncome'] = $totalIncome;
$_SESSION['todayIncome'] = $todayIncome;
$_SESSION['todayOrders'] = $todayOrders;
$_SESSION['orders'] = $translatedOrders; // 使用翻译后的订单数据
$_SESSION['adminInfo'] = $adminInfo; // 存储管理员信息到会话


// 关闭数据库连接
$conn->close();

// 重定向到dashboard.php
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
$scriptDir = $scriptName !== '' ? dirname($scriptName) : '/admin';
if ($scriptDir === '\\' || $scriptDir === '/' || $scriptDir === '.') {
    $scriptDir = '/admin';
}
header('Location: ' . rtrim($scriptDir, '/') . '/index.php');
exit;

