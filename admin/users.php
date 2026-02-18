<?php
// 引入数据库配置文件
require_once '../config/config.php';

//session_start();  //会话

if (!isset($_SESSION['totalIncome']) || !isset($_SESSION['todayIncome']) || !isset($_SESSION['todayOrders']) || !isset($_SESSION['orders']) || !isset($_SESSION['adminInfo'])) {
    // 如果会话变量不存在，重定向到groups.php以获取数据
    header("Location: groups.php");
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

// 关闭数据库连接
$conn->close();

// 定义API接口地址
$apiUrl = "https://api.ahfi.cn/api/getGreetingMessage?type=json";

// 生成本地问候语，避免外部接口异常直接暴露到页面。
function buildLocalGreetingMessage() {
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

    return array(
        'greeting' => $greeting,
        'tip' => $tips[array_rand($tips)],
        'currentTime' => date('Y-m-d H:i:s')
    );
}

// 使用cURL发起请求并做容错，失败时回退本地问候语。
function getGreetingMessage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $response = curl_exec($ch);
    if ($response === false) {
        error_log('admin/users.php 问候语接口请求失败: ' . curl_error($ch));
        curl_close($ch);
        return buildLocalGreetingMessage();
    }
    curl_close($ch);

    $decoded = json_decode($response, true);
    if (!is_array($decoded) || !isset($decoded['data']) || !is_array($decoded['data'])) {
        error_log('admin/users.php 问候语接口返回格式异常');
        return buildLocalGreetingMessage();
    }

    $greeting = trim((string)($decoded['data']['greeting'] ?? ''));
    $tip = trim((string)($decoded['data']['tip'] ?? ''));
    $currentTime = trim((string)($decoded['data']['currentTime'] ?? ''));

    if ($greeting === '' || $tip === '') {
        return buildLocalGreetingMessage();
    }

    return array(
        'greeting' => $greeting,
        'tip' => $tip,
        'currentTime' => $currentTime === '' ? date('Y-m-d H:i:s') : $currentTime
    );
}

$greetingData = getGreetingMessage($apiUrl);
$greeting = $greetingData['greeting'];
$warmWords = $greetingData['tip'];
$currentTime = $greetingData['currentTime'];

?>
