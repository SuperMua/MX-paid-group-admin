<?php
require_once 'login_check.php';
require_once 'virtual_data_helper.php';
// 引入数据库配置文件
require_once __DIR__ . '/../config/config.php';

//session_start();  //会话

if (!isset($_SESSION['totalIncome']) || !isset($_SESSION['todayIncome']) || !isset($_SESSION['todayOrders']) || !isset($_SESSION['orders']) || !isset($_SESSION['adminInfo'])) {
    // 如果会话变量不存在，重定向到groups.php以获取数据
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $scriptDir = $scriptName !== '' ? dirname($scriptName) : '/admin';
    if ($scriptDir === '\\' || $scriptDir === '/' || $scriptDir === '.') {
        $scriptDir = '/admin';
    }
    header('Location: ' . rtrim($scriptDir, '/') . '/groups.php');
    exit;
}

$totalIncome = $_SESSION['totalIncome'];
$todayIncome = $_SESSION['todayIncome'];
$todayOrders = $_SESSION['todayOrders'];
$orders = $_SESSION['orders'];
$adminInfo = $_SESSION['adminInfo']; // 获取管理员信息
//var_dump($_SESSION);

//var_dump($specificSessionData);
// 如果需要清理会话数据
unset($_SESSION['totalIncome']);
unset($_SESSION['todayIncome']);
unset($_SESSION['todayOrders']);
//session_destroy(); // 销毁会话

if (vd_is_enabled()) {
    $snapshot = vd_get_dashboard_snapshot();
    $unreviewedCount = $snapshot['unreviewed_count'];
} else {
    // 查询待审核的不同IP地址的数量
    $sql = "SELECT COUNT(DISTINCT ip_address) AS unreviewedCount FROM images WHERE status = 'pending'";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $unreviewedCount = $row['unreviewedCount'];

        // 返回待审核的不同IP地址的数量
        // echo $unreviewedCount;
    } else {
        $unreviewedCount = 0;
        error_log('admin/users.php 查询待审核数量失败: ' . $conn->error);
    }
}

// 关闭数据库连接
$conn->close();

// 本地生成问候语
date_default_timezone_set('Asia/Shanghai');
$hour = (int) date('H');
if ($hour >= 6 && $hour < 12) {
    $greeting = '早上好';
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = '下午好';
} elseif ($hour >= 18 && $hour < 23) {
    $greeting = '晚上好';
} else {
    $greeting = '夜深了';
}

$tips = array(
    '建议先处理待审核任务，再巡检支付与模板配置。',
    '今日重点：关注新增访客与支付转化率变化。',
    '保持配置与素材一致，可减少用户进群阻塞。',
    '建议每晚备份一次数据库，确保运营数据安全。'
);
$warmWords = $tips[array_rand($tips)];

?>
