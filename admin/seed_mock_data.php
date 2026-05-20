<?php
/**
 * 一键生成7天丰富模拟数据，填充控制台所有图表
 * 运行方式: php seed_mock_data.php  或  浏览器访问 admin/seed_mock_data.php
 */
$mysqli = new mysqli('127.0.0.1', 'qun555', '2D7W64zBm5e3j1AA', 'qun555');
if ($mysqli->connect_error) {
    die("连接失败: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 清空旧数据
$mysqli->query("TRUNCATE TABLE orders");
$mysqli->query("TRUNCATE TABLE visitors");

echo "旧数据已清空\n";

// ============ 配置 ============
$names = ['张三', '李四', '王五', '赵六', '陈七', '周八', '吴九', '郑十',
    '刘明', '黄丽', '孙子涵', '欧阳静', '慕容雪', '令狐冲', '张小凡', '陆雪琪',
    '林动', '萧炎', '叶凡', '石昊', '韩立', '秦羽', '方平', '罗峰',
    '许七安', '陈平安', '宁毅', '范闲', '徐凤年', '李淳罡'];

$locations = ['深圳', '杭州', '北京', '上海', '成都', '广州', '武汉', '南京', '重庆', '西安',
    '长沙', '郑州', '苏州', '天津', '东莞', '合肥', '厦门', '青岛', '沈阳', '大连'];

$groups = ['VIP交流群', '资源分享群', '技术讨论群', '创业互助群', '行业人脉群', '高端社群'];

$ips = [];
for ($i = 0; $i < 80; $i++) {
    $ips[] = rand(1, 223) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254);
}

// ============ 生成7天订单数据 ============
$days = [];
for ($i = 6; $i >= 0; $i--) {
    $days[] = date('Y-m-d', strtotime("-{$i} day"));
}

// 每天订单数递增，制造增长趋势
$ordersPerDay = [12, 15, 18, 20, 25, 28, 32]; // 7天共 150 单

$orderValues = [];
$totalInserted = 0;

foreach ($days as $idx => $day) {
    $count = $ordersPerDay[$idx];
    for ($j = 0; $j < $count; $j++) {
        $name = $names[array_rand($names)];
        $loc = $locations[array_rand($locations)];
        $ip = $ips[array_rand($ips)];
        $group = $groups[array_rand($groups)];

        // 85% 已支付，15% 未支付
        $isPaid = (mt_rand(1, 100) <= 85);
        $status = $isPaid ? '已支付' : '未支付';

        // 金额: 9.9 ~ 99.9
        $money = round(mt_rand(99, 999) / 10, 2);

        // 支付方式: 65% 微信, 35% 支付宝
        $method = (mt_rand(1, 100) <= 65) ? 'wxpay' : 'alipay';

        // 时间: 集中在 8:00~23:00
        $hour = mt_rand(8, 23);
        $min = str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT);
        $sec = str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT);
        $paymentTime = "{$day} {$hour}:{$min}:{$sec}";

        // 订单号
        $orderNo = date('YmdHis', strtotime($paymentTime)) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $escapedName = $mysqli->real_escape_string($name);
        $escapedLoc = $mysqli->real_escape_string($loc);

        $sql = "INSERT INTO orders (order_number, name, money, payment_method, payment_status, payment_time, ip_address, ip_location)
                VALUES ('$orderNo', '$escapedName', $money, '$method', '$status', '$paymentTime', '$ip', '$escapedLoc')";
        $mysqli->query($sql);
        $totalInserted++;
    }
}

echo "订单已插入: {$totalInserted} 条\n";

// ============ 生成访客数据 ============
$visitorsPerDay = [20, 25, 30, 35, 42, 50, 58]; // 共 260 个独立访客事件

$totalVisitors = 0;
foreach ($days as $idx => $day) {
    $count = $visitorsPerDay[$idx];
    for ($j = 0; $j < $count; $j++) {
        $ip = $ips[array_rand($ips)];
        $loc = $locations[array_rand($locations)];
        $hour = mt_rand(6, 23);
        $min = str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT);
        $sec = str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT);
        $visitTime = "{$day} {$hour}:{$min}:{$sec}";
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $pageUrl = (mt_rand(1, 3) === 1) ? 'https://example.com/pay' : 'https://example.com/';

        $escapedLoc = $mysqli->real_escape_string($loc);
        $sql = "INSERT INTO visitors (ip_address, ip_location, visit_time, user_agent, page_url)
                VALUES ('$ip', '$escapedLoc', '$visitTime', '$ua', '$pageUrl')";
        $mysqli->query($sql);
        $totalVisitors++;
    }
}

echo "访客已插入: {$totalVisitors} 条\n";

// ============ 验证 ============
$r = $mysqli->query("SELECT COUNT(*) as cnt FROM orders");
$row = $r->fetch_assoc();
echo "\n=== 数据汇总 ===\n";
echo "总订单: {$row['cnt']}\n";

$r = $mysqli->query("SELECT COUNT(*) as cnt FROM visitors");
$row = $r->fetch_assoc();
echo "总访客记录: {$row['cnt']}\n";

$r = $mysqli->query("SELECT DATE(payment_time) as dt, COUNT(*) as cnt, ROUND(SUM(CASE WHEN payment_status='已支付' THEN money ELSE 0 END),2) as income FROM orders GROUP BY dt ORDER BY dt");
echo "\n每日订单与收入:\n";
while ($row = $r->fetch_assoc()) {
    echo "  {$row['dt']}  订单{$row['cnt']}  收入¥{$row['income']}\n";
}

$r = $mysqli->query("SELECT payment_method, COUNT(*) as cnt FROM orders WHERE payment_status='已支付' GROUP BY payment_method");
echo "\n支付方式分布:\n";
while ($row = $r->fetch_assoc()) {
    echo "  {$row['payment_method']}: {$row['cnt']} 单\n";
}

$r = $mysqli->query("SELECT payment_status, COUNT(*) as cnt FROM orders GROUP BY payment_status");
echo "\n支付状态分布:\n";
while ($row = $r->fetch_assoc()) {
    echo "  {$row['payment_status']}: {$row['cnt']} 单\n";
}

$mysqli->close();
echo "\n✅ 模拟数据生成完成！\n";
