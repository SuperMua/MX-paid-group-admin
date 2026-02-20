<?php
require_once 'login_check.php';
require_once 'virtual_data_helper.php';
// 引入数据库配置文件
require '../config/config.php';

// 查询今日访客数量
function getTodayVisitors($conn) {
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(DISTINCT ip_address) AS count FROM visitors WHERE DATE(visit_time) = '$today'";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    } else {
        return 0;
    }
}

// 查询昨日访客数量
function getYesterdayVisitors($conn) {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $sql = "SELECT COUNT(DISTINCT ip_address) AS count FROM visitors WHERE DATE(visit_time) = '$yesterday'";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    } else {
        return 0;
    }
}

// 查询所有访客记录（支持分页）
function getAllVisitors($conn, $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;
    $sql = "SELECT ip_address, ip_location, user_agent, visit_time, page_url FROM visitors ORDER BY visit_time DESC LIMIT $offset, $limit";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// 查询总访问量
function getTotalVisitors($conn) {
    $sql = "SELECT COUNT(DISTINCT ip_address) AS count FROM visitors";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    } else {
        return 0;
    }
}

// 获取总记录数
function getTotalRecords($conn) {
    $sql = "SELECT COUNT(*) as total FROM visitors";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    } else {
        return 0;
    }
}

// 获取当前页码
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
// 每页显示的记录数（桌面端按 3 列布局可完整铺满）
$limit = 12;

if (vd_is_enabled()) {
    if (isset($_POST['clear_all'])) {
        vd_clear_visitors();
        echo "操作成功";
        exit;
    }

    $mockPayload = vd_get_visitors_payload($page, $limit);
    $allVisitors = $mockPayload['allVisitors'];
    $totalVisitors = $mockPayload['totalVisitors'];
    $todayVisitors = $mockPayload['todayVisitors'];
    $yesterdayVisitors = $mockPayload['yesterdayVisitors'];
    $totalRecords = $mockPayload['totalRecords'];
    $totalPages = $mockPayload['totalPages'];
    $conn->close();
    return;
}

// 获取总访问量
$totalVisitors = getTotalVisitors($conn);

// 获取所有访客记录（分页）
$allVisitors = getAllVisitors($conn, $page, $limit);

// 获取今日访客数量
$todayVisitors = getTodayVisitors($conn);

// 获取昨日访客数量
$yesterdayVisitors = getYesterdayVisitors($conn);

// 获取总记录数
$totalRecords = getTotalRecords($conn);
// 计算总页数
$totalPages = ceil($totalRecords / $limit);



// 检查是否接收到清空请求
if (isset($_POST['clear_all'])) {
    // 执行清空表的SQL语句
    $sql = "TRUNCATE TABLE visitors"; // 或使用 DELETE FROM visitors
    if ($conn->query($sql) === TRUE) {
        echo "操作成功";
    } else {
        echo "操作失败: " . $conn->error;
    }
}

// 关闭连接
$conn->close();
?>
