<?php
// dashboard_data.php – 计算控制台所有数据，供 index.php 服务端渲染使用
// 独立创建数据库连接（因为 users.php 已经关闭了之前的连接）
$dashConn = new mysqli('127.0.0.1', 'qun555', '2D7W64zBm5e3j1AA', 'qun555');
if (!$dashConn->connect_error) {
    $dashConn->set_charset("utf8mb4");
}

// Always use real DB data for the dashboard — Virtual Data is a separate demo feature
$r = $dashConn->query("SELECT * FROM orders ORDER BY payment_time DESC");
$dashOrders = $r ? $r->fetch_all(MYSQLI_ASSOC) : array();

$r = $dashConn->query("SELECT * FROM visitors ORDER BY visit_time DESC");
$dashVisitors = $r ? $r->fetch_all(MYSQLI_ASSOC) : array();

$dashConn->close();

// ---- Summary ----
$dashTotalIncome = 0; $dashTodayIncome = 0; $dashTodayOrders = 0;
$dashPaidCount = 0; $dashUnpaidCount = 0;
$dashToday = date('Y-m-d');
$dashYesterday = date('Y-m-d', strtotime('-1 day'));
$dashTodayIps = array(); $dashYesterdayIps = array(); $dashAllIps = array();
$dashPaidIps = array();

foreach ($dashOrders as $o) {
    $status = $o['payment_status'] ?? '';
    if ($status === '已支付') {
        $dashPaidCount++;
        $dashTotalIncome += (float) $o['money'];
        $dashPaidIps[$o['ip_address']] = 1;
        if (strpos($o['payment_time'], $dashToday) === 0) {
            $dashTodayIncome += (float) $o['money'];
            $dashTodayOrders++;
        }
    } else {
        $dashUnpaidCount++;
    }
}
foreach ($dashVisitors as $v) {
    $dashAllIps[$v['ip_address']] = 1;
    if (strpos($v['visit_time'], $dashToday) === 0) $dashTodayIps[$v['ip_address']] = 1;
    if (strpos($v['visit_time'], $dashYesterday) === 0) $dashYesterdayIps[$v['ip_address']] = 1;
}

$dashTotalVisitors = count($dashAllIps);
$dashTodayVisitors = count($dashTodayIps);
$dashYesterdayVisitors = count($dashYesterdayIps);
$dashTotalOrders = count($dashOrders);
$dashConversion = $dashTotalVisitors > 0 ? min(100, round((count($dashPaidIps) / $dashTotalVisitors) * 100, 1)) : 0;
$dashAvgOrder = $dashPaidCount > 0 ? round($dashTotalIncome / $dashPaidCount, 2) : 0;

// ---- 7-day trend ----
$dashTrend = array();
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} day"));
    $income = 0; $orderCnt = 0; $vIps = array();
    foreach ($dashOrders as $o) {
        if (strpos($o['payment_time'], $d) === 0 && ($o['payment_status'] ?? '') === '已支付') {
            $income += (float) $o['money'];
            $orderCnt++;
        }
    }
    foreach ($dashVisitors as $v) {
        if (strpos($v['visit_time'], $d) === 0) $vIps[$v['ip_address']] = 1;
    }
    $dashTrend[] = array('date' => date('m/d', strtotime($d)), 'income' => round($income, 2), 'orders' => $orderCnt, 'visitors' => count($vIps));
}

// ---- Payment methods ----
$wx = 0; $ali = 0;
foreach ($dashOrders as $o) {
    $m = $o['payment_method'] ?? '';
    if ($m === 'wxpay' || $m === '微信') $wx++;
    elseif ($m === 'alipay' || $m === '支付宝') $ali++;
}

// ---- Top locations ----
$locMap = array();
foreach ($dashOrders as $o) {
    $loc = $o['ip_location'] ?? '未知';
    if (!isset($locMap[$loc])) $locMap[$loc] = 0;
    $locMap[$loc]++;
}
arsort($locMap);
$dashTopLocs = array_slice($locMap, 0, 5, true);

// ---- Recent orders ----
$dashRecentOrders = array_slice($dashOrders, 0, 5);

// ---- Hourly ----
$dashHourly = array_fill(0, 24, 0);
foreach ($dashOrders as $o) {
    if (strpos($o['payment_time'], $dashToday) === 0) {
        $h = (int) substr($o['payment_time'], 11, 2);
        $dashHourly[$h]++;
    }
}

// 格式化为 number_format
$dashTotalIncomeFmt = number_format($dashTotalIncome, 2);
$dashTodayIncomeFmt = number_format($dashTodayIncome, 2);
$dashAvgOrderFmt = number_format($dashAvgOrder, 2);
