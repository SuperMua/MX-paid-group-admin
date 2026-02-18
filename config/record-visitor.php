<?php
// 引入数据库配置文件
require_once 'config.php';

// 创建数据库连接
$conn = new mysqli($host, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}


// 获取用户IP地址
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function getIPLocation($ip) {
    // 1. 接口配置（Vore-TOP IP解析接口）
    $apiUrl = "https://api.vore.top/api/IPdata?ip=" . urlencode($ip);
    
    // 2. 增强HTTP请求可靠性（设置超时、避免阻塞）
    $context = stream_context_create([
        'http' => [
            'timeout' => 2,          // 2秒超时，防止接口无响应导致页面卡顿
            'method' => 'GET',       // 明确请求方法
            'header' => 'Accept: application/json' // 声明接收JSON格式
        ]
    ]);
    
    // 3. 调用接口并捕获异常
    $response = @file_get_contents($apiUrl, false, $context);
    // 接口调用失败（超时/网络错误/无响应）
    if (!$response) {
        return '未知';
    }

    // 4. 解析JSON响应
    $data = json_decode($response, true);
    // JSON解析失败（格式错误/空响应）
    if (json_last_error()!== JSON_ERROR_NONE) {
        return '未知';
    }

    // 5. 校验核心字段是否存在（确保数据结构符合预期）
    if (!isset($data['ipdata']['info1'], $data['ipdata']['info2'])) {
        return '未知';
    }

    // 6. 提取并处理字段（过滤异常值）
    $province = trim($data['ipdata']['info1']); 
    $city = trim($data['ipdata']['info2']);   
    
    // 定义需要过滤的异常城市值（可根据接口返回扩展）
    $invalidCityValues = ['基站', '未知', '内网', ''];
    
    // 7. 逻辑判断：正常城市拼接"省+市"，异常值仅返回省份
    if (in_array($city, $invalidCityValues)) {
        return $province!== ''? $province : '未知';
    } else {
        return $province.''. $city;
    }
}

// 获取POST请求的JSON数据
$data = json_decode(file_get_contents('php://input'), true);

// 提取数据
$ipAddress = $conn->real_escape_string(getUserIP());
$ipLocation = $conn->real_escape_string(getIPLocation($ipAddress));
$userAgent = $conn->real_escape_string($data['userAgent']);
$visitTime = $conn->real_escape_string($data['visitTime']);
$pageUrl = $conn->real_escape_string($data['pageUrl']);

// 插入数据到visitors表
$sql = "INSERT INTO visitors (ip_address, ip_location, user_agent, visit_time, page_url) VALUES ('$ipAddress', '$ipLocation', '$userAgent', '$visitTime', '$pageUrl')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['message' => '访客记录成功']);
} else {
    echo json_encode(['message' => '访客记录失败', 'error' => $conn->error]);
}

// 关闭连接
$conn->close();
?>