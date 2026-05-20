<?php
require_once 'login_check.php';
require_once 'virtual_data_helper.php';
require __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if (vd_is_enabled()) {
    $dataset = vd_get_dataset();
    $orders = isset($dataset['orders']) ? $dataset['orders'] : array();
    $visitors = isset($dataset['visitors']) ? $dataset['visitors'] : array();
} else {
    $r = $conn->query("SELECT * FROM orders ORDER BY payment_time DESC");
    $orders = $r ? $r->fetch_all(MYSQLI_ASSOC) : array();

    $r = $conn->query("SELECT * FROM visitors ORDER BY visit_time DESC");
    $visitors = $r ? $r->fetch_all(MYSQLI_ASSOC) : array();

    $conn->close();
}

// ---- 7-day revenue & order trend ----
$trend = array();
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} day"));
    $income = 0; $orderCnt = 0; $visitorCnt = 0; $visitorIps = array();
    foreach ($orders as $o) {
        if (strpos($o['payment_time'], $d) === 0 && (isset($o['payment_status']) ? $o['payment_status'] === '已支付' : true)) {
            $income += (float) $o['money'];
            $orderCnt++;
        }
    }
    foreach ($visitors as $v) {
        if (strpos($v['visit_time'], $d) === 0) {
            $visitorIps[$v['ip_address']] = 1;
        }
    }
    $trend[] = array(
        'date' => date('m/d', strtotime($d)),
        'income' => round($income, 2),
        'orders' => $orderCnt,
        'visitors' => count($visitorIps)
    );
}

// ---- Payment method breakdown ----
$wx = 0; $ali = 0;
foreach ($orders as $o) {
    $method = $o['payment_method'] ?? '';
    if ($method === 'wxpay' || $method === '微信') $wx++;
    elseif ($method === 'alipay' || $method === '支付宝') $ali++;
}

// ---- Payment status ratio ----
$paid = 0; $unpaid = 0;
foreach ($orders as $o) {
    $status = $o['payment_status'] ?? '';
    if ($status === '已支付') $paid++;
    else $unpaid++;
}

// ---- Top locations ----
$locMap = array();
foreach ($orders as $o) {
    $loc = $o['ip_location'] ?? '未知';
    if (!isset($locMap[$loc])) $locMap[$loc] = 0;
    $locMap[$loc]++;
}
arsort($locMap);
$topLocs = array_slice($locMap, 0, 5);

// ---- Recent orders (latest 5) ----
$recentOrders = array_slice($orders, 0, 5);

// ---- Hourly today ----
$todayStr = date('Y-m-d');
$hourly = array_fill(0, 24, 0);
foreach ($orders as $o) {
    if (strpos($o['payment_time'], $todayStr) === 0) {
        $h = (int) substr($o['payment_time'], 11, 2);
        $hourly[$h]++;
    }
}

// ---- Summary ----
$totalIncome = 0; $todayIncome = 0; $todayOrders = 0; $todayVisitors = 0; $yesterdayVisitors = 0;
$todayIps = array(); $yesterdayIps = array();
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

foreach ($orders as $o) {
    $status = $o['payment_status'] ?? '';
    if ($status === '已支付') {
        $totalIncome += (float) $o['money'];
        if (strpos($o['payment_time'], $today) === 0) {
            $todayIncome += (float) $o['money'];
            $todayOrders++;
        }
    }
}
foreach ($visitors as $v) {
    if (strpos($v['visit_time'], $today) === 0) {
        $todayIps[$v['ip_address']] = 1;
    }
    if (strpos($v['visit_time'], $yesterday) === 0) {
        $yesterdayIps[$v['ip_address']] = 1;
    }
}
$todayVisitors = count($todayIps);
$yesterdayVisitors = count($yesterdayIps);

// Total distinct visitors all time
$allIps = array();
foreach ($visitors as $v) { $allIps[$v['ip_address']] = 1; }
$totalVisitors = count($allIps);

// Conversion rate: unique IPs with paid orders / total unique visitors
$paidIps = array();
foreach ($orders as $o) {
    if (($o['payment_status'] ?? '') === '已支付') {
        $paidIps[$o['ip_address']] = 1;
    }
}
$conversionRate = $totalVisitors > 0 ? min(100, round((count($paidIps) / $totalVisitors) * 100, 1)) : 0;

echo json_encode(array(
    'summary' => array(
        'total_income' => number_format($totalIncome, 2, '.', ''),
        'today_income' => number_format($todayIncome, 2, '.', ''),
        'today_orders' => $todayOrders,
        'today_visitors' => $todayVisitors,
        'yesterday_visitors' => $yesterdayVisitors,
        'total_visitors' => $totalVisitors,
        'total_orders' => count($orders),
        'conversion_rate' => $conversionRate,
        'unreviewed_count' => 0
    ),
    'trend' => $trend,
    'payment_methods' => array(
        array('name' => '微信支付', 'value' => $wx),
        array('name' => '支付宝', 'value' => $ali)
    ),
    'payment_status' => array(
        array('name' => '已支付', 'value' => $paid),
        array('name' => '未支付', 'value' => $unpaid)
    ),
    'top_locations' => array_map(function($k, $v) {
        return array('name' => $k, 'count' => $v);
    }, array_keys($topLocs), $topLocs),
    'recent_orders' => array_map(function($o) {
        return array(
            'name' => $o['name'] ?? '',
            'money' => $o['money'] ?? '0.00',
            'method' => ($o['payment_method'] ?? '') === 'wxpay' || ($o['payment_method'] ?? '') === '微信' ? '微信' : '支付宝',
            'status' => $o['payment_status'] ?? '未知',
            'time' => $o['payment_time'] ?? ''
        );
    }, $recentOrders),
    'hourly' => $hourly
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
