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
    echo "查询失败: " . $conn->error;
}

// 关闭数据库连接
$conn->close();

// 定义API接口地址
$apiUrl = "https://api.ahfi.cn/api/getGreetingMessage?type=json";
// 使用cURL发起请求
function getGreetingMessage($url) {
    $ch = curl_init(); // 初始化cURL
    curl_setopt($ch, CURLOPT_URL, $url); // 设置请求的URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将返回的数据作为字符串返回
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 设置超时时间

    $response = curl_exec($ch); // 执行cURL请求
    if (curl_errno($ch)) {
        // 如果请求失败，返回错误信息
        return array('error' => '请求失败：' . curl_error($ch));
    }
    curl_close($ch); // 关闭cURL

    // 解析返回的JSON数据
    return json_decode($response, true);
}

// 调用函数获取问候语
$result = getGreetingMessage($apiUrl);

// 检查返回结果
if (isset($result['error'])) {
    // 如果有错误，显示错误信息
    echo "错误：" . $result['error'];
} else {
    // 提取问候语和提示信息
    $greeting = $result['data']['greeting'];
    $warmWords = $result['data']['tip'];
    $currentTime = $result['data']['currentTime'];

    // 显示问候语和提示信息
    //echo "当前时间：" . $currentTime . "<br>";
    //echo "问候语：" . $greeting . "<br>";
    //echo "提示信息：" . $warmWords . "<br>";
}

?>