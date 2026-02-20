<?php
require_once 'login_check.php';

if (!class_exists('VirtualArrayResult')) {
    class VirtualArrayResult {
        public $num_rows = 0;
        private $rows = array();
        private $index = 0;

        public function __construct($rows) {
            $this->rows = is_array($rows) ? array_values($rows) : array();
            $this->num_rows = count($this->rows);
        }

        public function fetch_assoc() {
            if ($this->index >= $this->num_rows) {
                return null;
            }
            $row = $this->rows[$this->index];
            $this->index++;
            return $row;
        }

        public function fetch_all($mode = null) {
            return $this->rows;
        }

        public function rewind() {
            $this->index = 0;
        }
    }
}

function vd_boot_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['virtual_data'])) {
        $_SESSION['virtual_data'] = array(
            'enabled' => false,
            'seed' => null,
            'generated_at' => null,
            'updated_at' => null,
            'dataset' => null,
            'snapshot_override' => null
        );
    }
}

function vd_now_string() {
    return date('Y-m-d H:i:s');
}

function vd_random_seed() {
    return (int) (microtime(true) * 1000) + mt_rand(1000, 9999);
}

function vd_parse_float($value, $default) {
    if ($value === '' || $value === null) {
        return (float) $default;
    }
    if (!is_numeric($value)) {
        return (float) $default;
    }
    return (float) $value;
}

function vd_parse_int($value, $default) {
    if ($value === '' || $value === null) {
        return (int) $default;
    }
    if (!is_numeric($value)) {
        return (int) $default;
    }
    return (int) $value;
}

function vd_normalize_snapshot_override($incoming) {
    $data = is_array($incoming) ? $incoming : array();
    $totalIncome = max(0, vd_parse_float($data['total_income'] ?? null, 0));
    $todayIncome = max(0, vd_parse_float($data['today_income'] ?? null, 0));
    $todayOrders = max(0, vd_parse_int($data['today_orders'] ?? null, 0));
    $yesterdayVisitors = max(0, vd_parse_int($data['yesterday_visitors'] ?? null, 0));
    $todayVisitors = max(0, vd_parse_int($data['today_visitors'] ?? null, 0));
    $unreviewedCount = max(0, vd_parse_int($data['unreviewed_count'] ?? null, 0));

    return array(
        'total_income' => number_format($totalIncome, 2, '.', ''),
        'today_income' => number_format($todayIncome, 2, '.', ''),
        'today_orders' => $todayOrders,
        'yesterday_visitors' => $yesterdayVisitors,
        'today_visitors' => $todayVisitors,
        'unreviewed_count' => $unreviewedCount
    );
}

function vd_set_snapshot_override($snapshot) {
    vd_boot_session();
    $_SESSION['virtual_data']['snapshot_override'] = vd_normalize_snapshot_override($snapshot);
    $_SESSION['virtual_data']['updated_at'] = vd_now_string();
}

function vd_clear_snapshot_override() {
    vd_boot_session();
    $_SESSION['virtual_data']['snapshot_override'] = null;
    $_SESSION['virtual_data']['updated_at'] = vd_now_string();
}

function vd_get_snapshot_override() {
    vd_boot_session();
    if (empty($_SESSION['virtual_data']['snapshot_override']) || !is_array($_SESSION['virtual_data']['snapshot_override'])) {
        return null;
    }
    return $_SESSION['virtual_data']['snapshot_override'];
}

function vd_set_enabled($enabled) {
    vd_boot_session();
    $_SESSION['virtual_data']['enabled'] = $enabled ? true : false;
    $_SESSION['virtual_data']['updated_at'] = vd_now_string();
    if ($enabled && empty($_SESSION['virtual_data']['dataset'])) {
        vd_regenerate_dataset();
    }
}

function vd_is_enabled() {
    vd_boot_session();
    return !empty($_SESSION['virtual_data']['enabled']);
}

function vd_get_seed() {
    vd_boot_session();
    if (empty($_SESSION['virtual_data']['seed'])) {
        $_SESSION['virtual_data']['seed'] = vd_random_seed();
    }
    return (int) $_SESSION['virtual_data']['seed'];
}

function vd_pick_upload_filenames() {
    $files = glob(__DIR__ . '/../upload/*.{jpg,jpeg,png,gif,webp,JPG,JPEG,PNG,GIF,WEBP}', GLOB_BRACE);
    $names = array();
    if (is_array($files)) {
        foreach ($files as $file) {
            if (is_file($file)) {
                $names[] = basename($file);
            }
        }
    }

    if (empty($names)) {
        $names = array('demo_1.jpg', 'demo_2.jpg', 'demo_3.jpg', 'demo_4.jpg');
    }
    return $names;
}

function vd_make_ip() {
    return mt_rand(36, 223) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254);
}

function vd_pick($list) {
    return $list[mt_rand(0, count($list) - 1)];
}

function vd_clamp($value, $min, $max) {
    return max($min, min($max, $value));
}

function vd_parse_text_list($text, $fallback) {
    $raw = preg_split('/[\r\n,，;；]+/u', (string) $text);
    $result = array();
    if (is_array($raw)) {
        foreach ($raw as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $result[] = $item;
            }
        }
    }
    return !empty($result) ? $result : $fallback;
}

function vd_parse_price_list($text, $fallback) {
    $raw = vd_parse_text_list($text, array());
    $numbers = array();
    foreach ($raw as $item) {
        $val = (float) $item;
        if ($val > 0) {
            $numbers[] = $val;
        }
    }
    return !empty($numbers) ? $numbers : $fallback;
}

function vd_default_generator_options() {
    return array(
        'order_count' => 96,
        'visitor_count' => 260,
        'review_group_count' => 22,
        'review_images_min' => 2,
        'review_images_max' => 6,
        'paid_ratio' => 78,
        'pending_ratio' => 40,
        'approved_ratio' => 42,
        'rejected_ratio' => 18,
        'order_days_range' => 14,
        'visitor_days_range' => 8,
        'review_days_range' => 12,
        'today_order_ratio' => 16,
        'payment_method_wx_ratio' => 55,
        'order_names' => '付费订单,会员订单,进群订单,推广订单,VIP入群,活动订单,渠道订单,加急订单',
        'locations' => '广东省 深圳市,浙江省 杭州市,江苏省 南京市,四川省 成都市,北京市 北京市,上海市 上海市,湖北省 武汉市,福建省 厦门市,陕西省 西安市,河南省 郑州市',
        'user_agents' => 'iPhone Safari,Android Chrome,Windows Chrome,Mac Safari,HarmonyOS Browser,Xiaomi Browser,Edge 126',
        'pages' => '/public/home_v1.php,/public/home_v2.php,/public/check.php,/public/pay.php',
        'price_list' => '9.9,19.9,29.9,39.9,49.9,59.9,99',
        'reviewer_names' => '管理员,系统审核员,运营专员'
    );
}

function vd_get_generator_preset_options($preset) {
    $base = vd_default_generator_options();
    $preset = trim((string) $preset);
    if ($preset === 'high_conversion') {
        $base['order_count'] = 120;
        $base['visitor_count'] = 220;
        $base['paid_ratio'] = 90;
        $base['today_order_ratio'] = 22;
        $base['pending_ratio'] = 28;
        $base['approved_ratio'] = 58;
        $base['rejected_ratio'] = 14;
    } elseif ($preset === 'traffic_burst') {
        $base['order_count'] = 80;
        $base['visitor_count'] = 420;
        $base['paid_ratio'] = 52;
        $base['today_order_ratio'] = 12;
        $base['review_group_count'] = 34;
        $base['pending_ratio'] = 45;
        $base['approved_ratio'] = 35;
        $base['rejected_ratio'] = 20;
    } elseif ($preset === 'strict_audit') {
        $base['order_count'] = 92;
        $base['visitor_count'] = 260;
        $base['paid_ratio'] = 72;
        $base['review_group_count'] = 28;
        $base['pending_ratio'] = 48;
        $base['approved_ratio'] = 30;
        $base['rejected_ratio'] = 22;
    } elseif ($preset === 'cold_start') {
        $base['order_count'] = 36;
        $base['visitor_count'] = 86;
        $base['review_group_count'] = 10;
        $base['review_images_min'] = 1;
        $base['review_images_max'] = 3;
        $base['paid_ratio'] = 62;
        $base['today_order_ratio'] = 26;
    }
    return $base;
}

function vd_merge_generator_options($preset, $overrides) {
    $base = vd_get_generator_preset_options($preset);
    if (!is_array($overrides)) {
        return $base;
    }
    foreach ($overrides as $key => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        $base[$key] = $value;
    }
    return $base;
}

function vd_generate_dataset($seed, $options = array()) {
    mt_srand((int) $seed);
    $opts = vd_merge_generator_options($options['preset'] ?? 'balanced', $options);

    $orderNames = vd_parse_text_list($opts['order_names'] ?? '', array('付费订单', '会员订单', '进群订单'));
    $locations = vd_parse_text_list($opts['locations'] ?? '', array('广东省 深圳市', '浙江省 杭州市', '江苏省 南京市'));
    $agents = vd_parse_text_list($opts['user_agents'] ?? '', array('iPhone Safari', 'Android Chrome', 'Windows Chrome'));
    $pages = vd_parse_text_list($opts['pages'] ?? '', array('/public/home_v1.php', '/public/home_v2.php', '/public/check.php', '/public/pay.php'));
    $orderAmounts = vd_parse_price_list($opts['price_list'] ?? '', array(9.90, 19.90, 29.90, 39.90));
    $reviewerNames = vd_parse_text_list($opts['reviewer_names'] ?? '', array('管理员'));
    $uploadNames = vd_pick_upload_filenames();

    $orderCount = vd_clamp((int) ($opts['order_count'] ?? 96), 1, 2000);
    $visitorCount = vd_clamp((int) ($opts['visitor_count'] ?? 260), 1, 5000);
    $reviewGroupCount = vd_clamp((int) ($opts['review_group_count'] ?? 22), 1, 500);
    $reviewImagesMin = vd_clamp((int) ($opts['review_images_min'] ?? 2), 1, 20);
    $reviewImagesMax = vd_clamp((int) ($opts['review_images_max'] ?? 6), $reviewImagesMin, 40);
    $paidRatio = vd_clamp((int) ($opts['paid_ratio'] ?? 78), 0, 100);
    $wxRatio = vd_clamp((int) ($opts['payment_method_wx_ratio'] ?? 55), 0, 100);
    $todayOrderRatio = vd_clamp((int) ($opts['today_order_ratio'] ?? 16), 0, 100);
    $orderDaysRange = vd_clamp((int) ($opts['order_days_range'] ?? 14), 1, 90);
    $visitorDaysRange = vd_clamp((int) ($opts['visitor_days_range'] ?? 8), 1, 90);
    $reviewDaysRange = vd_clamp((int) ($opts['review_days_range'] ?? 12), 1, 90);

    $pendingRatio = max(0, (int) ($opts['pending_ratio'] ?? 40));
    $approvedRatio = max(0, (int) ($opts['approved_ratio'] ?? 42));
    $rejectedRatio = max(0, (int) ($opts['rejected_ratio'] ?? 18));
    $statusSum = $pendingRatio + $approvedRatio + $rejectedRatio;
    if ($statusSum <= 0) {
        $pendingRatio = 40;
        $approvedRatio = 42;
        $rejectedRatio = 18;
        $statusSum = 100;
    }

    $orders = array();
    for ($i = 1; $i <= $orderCount; $i++) {
        $isToday = mt_rand(1, 100) <= $todayOrderRatio;
        $daysAgo = $isToday ? 0 : mt_rand(1, $orderDaysRange);
        $timestamp = strtotime('-' . $daysAgo . ' day');
        $timestamp = strtotime(date('Y-m-d', $timestamp) . ' ' . mt_rand(0, 23) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT));
        $paymentStatus = mt_rand(1, 100) <= $paidRatio ? '已支付' : '未支付';
        $paymentMethod = mt_rand(1, 100) <= $wxRatio ? 'wxpay' : 'alipay';
        $money = $orderAmounts[mt_rand(0, count($orderAmounts) - 1)];

        $orders[] = array(
            'id' => $i,
            'name' => vd_pick($orderNames),
            'order_number' => date('YmdHis', $timestamp) . str_pad((string) mt_rand(10, 999), 3, '0', STR_PAD_LEFT),
            'ip_address' => vd_make_ip(),
            'ip_location' => vd_pick($locations),
            'money' => number_format($money, 2, '.', ''),
            'payment_method' => $paymentMethod,
            'payment_time' => date('Y-m-d H:i:s', $timestamp),
            'payment_status' => $paymentStatus
        );
    }
    usort($orders, function ($a, $b) {
        return strcmp($b['payment_time'], $a['payment_time']);
    });

    $visitors = array();
    for ($i = 1; $i <= $visitorCount; $i++) {
        $daysAgo = mt_rand(0, $visitorDaysRange);
        $timestamp = strtotime('-' . $daysAgo . ' day');
        $timestamp = strtotime(date('Y-m-d', $timestamp) . ' ' . mt_rand(0, 23) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT));
        $visitors[] = array(
            'id' => $i,
            'ip_address' => vd_make_ip(),
            'ip_location' => vd_pick($locations),
            'user_agent' => vd_pick($agents),
            'visit_time' => date('Y-m-d H:i:s', $timestamp),
            'page_url' => vd_pick($pages)
        );
    }
    usort($visitors, function ($a, $b) {
        return strcmp($b['visit_time'], $a['visit_time']);
    });

    $reviewRows = array();
    $reviewId = 1;
    for ($g = 1; $g <= $reviewGroupCount; $g++) {
        $ip = vd_make_ip();
        $imgCount = mt_rand($reviewImagesMin, $reviewImagesMax);
        for ($j = 0; $j < $imgCount; $j++) {
            $daysAgo = mt_rand(0, $reviewDaysRange);
            $timestamp = strtotime('-' . $daysAgo . ' day');
            $timestamp = strtotime(date('Y-m-d', $timestamp) . ' ' . mt_rand(0, 23) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT));

            $randStatus = mt_rand(1, $statusSum);
            if ($randStatus <= $pendingRatio) {
                $status = 'pending';
            } elseif ($randStatus <= ($pendingRatio + $approvedRatio)) {
                $status = 'approved';
            } else {
                $status = 'rejected';
            }

            $filename = vd_pick($uploadNames);
            $reviewRows[] = array(
                'id' => $reviewId,
                'filename' => $filename,
                'upload_time' => date('Y-m-d H:i:s', $timestamp),
                'ip_address' => $ip,
                'file_path' => '../upload/' . $filename,
                'reviewer' => vd_pick($reviewerNames),
                'status' => $status,
                'ip_location' => vd_pick($locations)
            );
            $reviewId++;
        }
    }
    usort($reviewRows, function ($a, $b) {
        return strcmp($b['upload_time'], $a['upload_time']);
    });

    return array(
        'orders' => $orders,
        'visitors' => $visitors,
        'reviews' => $reviewRows
    );
}

function vd_regenerate_dataset($options = array()) {
    vd_boot_session();
    $seed = vd_random_seed();
    $_SESSION['virtual_data']['seed'] = $seed;
    $_SESSION['virtual_data']['dataset'] = vd_generate_dataset($seed, $options);
    $_SESSION['virtual_data']['snapshot_override'] = null;
    $_SESSION['virtual_data']['generated_at'] = vd_now_string();
    $_SESSION['virtual_data']['updated_at'] = vd_now_string();
}

function vd_get_dataset() {
    vd_boot_session();
    if (empty($_SESSION['virtual_data']['dataset']) || !is_array($_SESSION['virtual_data']['dataset'])) {
        $seed = vd_get_seed();
        $_SESSION['virtual_data']['dataset'] = vd_generate_dataset($seed);
        $_SESSION['virtual_data']['generated_at'] = vd_now_string();
        $_SESSION['virtual_data']['updated_at'] = vd_now_string();
    }
    return $_SESSION['virtual_data']['dataset'];
}

function vd_save_dataset($dataset) {
    vd_boot_session();
    $_SESSION['virtual_data']['dataset'] = $dataset;
    $_SESSION['virtual_data']['updated_at'] = vd_now_string();
}

function vd_count_distinct_by_day($rows, $date, $timeKey) {
    $set = array();
    foreach ($rows as $row) {
        if (strpos($row[$timeKey], $date) === 0) {
            $set[$row['ip_address']] = 1;
        }
    }
    return count($set);
}

function vd_get_orders_page($page, $perPage) {
    $dataset = vd_get_dataset();
    $orders = isset($dataset['orders']) ? $dataset['orders'] : array();

    $total = count($orders);
    $paid = 0;
    $unpaid = 0;
    foreach ($orders as $order) {
        if ($order['payment_status'] === '已支付') {
            $paid++;
        } else {
            $unpaid++;
        }
    }

    $offset = ($page - 1) * $perPage;
    $slice = array_slice($orders, $offset, $perPage);

    return array(
        'orders' => $slice,
        'countRow' => array(
            'total_orders' => $total,
            'paid_orders' => $paid,
            'unpaid_orders' => $unpaid
        )
    );
}

function vd_get_orders_all() {
    $dataset = vd_get_dataset();
    return isset($dataset['orders']) ? $dataset['orders'] : array();
}

function vd_get_dashboard_snapshot() {
    $snapshotOverride = vd_get_snapshot_override();
    if (is_array($snapshotOverride)) {
        return $snapshotOverride;
    }

    $dataset = vd_get_dataset();
    $orders = isset($dataset['orders']) ? $dataset['orders'] : array();
    $reviews = isset($dataset['reviews']) ? $dataset['reviews'] : array();
    $visitors = isset($dataset['visitors']) ? $dataset['visitors'] : array();

    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $totalIncome = 0;
    $todayIncome = 0;
    $todayOrders = 0;
    foreach ($orders as $order) {
        $money = (float) $order['money'];
        if ($order['payment_status'] === '已支付') {
            $totalIncome += $money;
            if (strpos($order['payment_time'], $today) === 0) {
                $todayIncome += $money;
                $todayOrders++;
            }
        }
    }

    $pendingIps = array();
    foreach ($reviews as $row) {
        if ($row['status'] === 'pending') {
            $pendingIps[$row['ip_address']] = 1;
        }
    }

    return array(
        'total_income' => number_format($totalIncome, 2, '.', ''),
        'today_income' => number_format($todayIncome, 2, '.', ''),
        'today_orders' => $todayOrders,
        'unreviewed_count' => count($pendingIps),
        'today_visitors' => vd_count_distinct_by_day($visitors, $today, 'visit_time'),
        'yesterday_visitors' => vd_count_distinct_by_day($visitors, $yesterday, 'visit_time')
    );
}

function vd_get_visitors_payload($page, $limit) {
    $dataset = vd_get_dataset();
    $visitors = isset($dataset['visitors']) ? $dataset['visitors'] : array();
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $offset = ($page - 1) * $limit;
    $slice = array_slice($visitors, $offset, $limit);
    $totalRecords = count($visitors);

    $distinctAll = array();
    foreach ($visitors as $row) {
        $distinctAll[$row['ip_address']] = 1;
    }

    return array(
        'allVisitors' => $slice,
        'totalVisitors' => count($distinctAll),
        'todayVisitors' => vd_count_distinct_by_day($visitors, $today, 'visit_time'),
        'yesterdayVisitors' => vd_count_distinct_by_day($visitors, $yesterday, 'visit_time'),
        'totalRecords' => $totalRecords,
        'totalPages' => max(1, (int) ceil($totalRecords / $limit))
    );
}

function vd_get_review_status_counts($reviews) {
    $approved = array();
    $pending = array();
    $rejected = array();
    foreach ($reviews as $row) {
        if ($row['status'] === 'approved') {
            $approved[$row['ip_address']] = 1;
        } elseif ($row['status'] === 'pending') {
            $pending[$row['ip_address']] = 1;
        } elseif ($row['status'] === 'rejected') {
            $rejected[$row['ip_address']] = 1;
        }
    }

    return array(
        'approved' => count($approved),
        'pending' => count($pending),
        'rejected' => count($rejected)
    );
}

function vd_get_review_list_payload($page, $recordsPerPage) {
    $dataset = vd_get_dataset();
    $reviews = isset($dataset['reviews']) ? $dataset['reviews'] : array();

    $groups = array();
    foreach ($reviews as $row) {
        $ip = $row['ip_address'];
        if (!isset($groups[$ip])) {
            $groups[$ip] = array(
                'ip_address' => $ip,
                'ip_location' => $row['ip_location'],
                'image_count' => 0,
                'latest_upload_time' => $row['upload_time'],
                'statuses' => array()
            );
        }
        $groups[$ip]['image_count']++;
        if ($row['upload_time'] > $groups[$ip]['latest_upload_time']) {
            $groups[$ip]['latest_upload_time'] = $row['upload_time'];
        }
        $groups[$ip]['statuses'][] = $row['status'];
    }

    $groupRows = array_values($groups);
    usort($groupRows, function ($a, $b) {
        return strcmp($b['latest_upload_time'], $a['latest_upload_time']);
    });

    $normalized = array();
    foreach ($groupRows as $row) {
        $statusList = $row['statuses'];
        $row['statuses'] = implode(',', $statusList);
        $normalized[] = $row;
    }

    $totalRecords = count($normalized);
    $offset = ($page - 1) * $recordsPerPage;
    $slice = array_slice($normalized, $offset, $recordsPerPage);
    $counts = vd_get_review_status_counts($reviews);

    return array(
        'rows' => $slice,
        'totalRecords' => $totalRecords,
        'totalPages' => max(1, (int) ceil($totalRecords / $recordsPerPage)),
        'totalApproved' => $counts['approved'],
        'totalPending' => $counts['pending'],
        'totalRejected' => $counts['rejected']
    );
}

function vd_get_review_details($ipAddress) {
    $dataset = vd_get_dataset();
    $reviews = isset($dataset['reviews']) ? $dataset['reviews'] : array();
    $rows = array();
    foreach ($reviews as $row) {
        if ($row['ip_address'] === $ipAddress) {
            $rows[] = $row;
        }
    }

    usort($rows, function ($a, $b) {
        return strcmp($b['upload_time'], $a['upload_time']);
    });

    return $rows;
}

function vd_update_review_status($id, $ipAddress, $status) {
    $dataset = vd_get_dataset();
    $reviews = isset($dataset['reviews']) ? $dataset['reviews'] : array();
    $updated = false;

    foreach ($reviews as &$row) {
        if ((int) $row['id'] === (int) $id && $row['ip_address'] === $ipAddress) {
            $row['status'] = $status;
            $updated = true;
            break;
        }
    }
    unset($row);

    $dataset['reviews'] = $reviews;
    vd_save_dataset($dataset);
    return $updated;
}

function vd_delete_review_by_id($id) {
    $dataset = vd_get_dataset();
    $reviews = isset($dataset['reviews']) ? $dataset['reviews'] : array();
    $before = count($reviews);

    $reviews = array_values(array_filter($reviews, function ($row) use ($id) {
        return (int) $row['id'] !== (int) $id;
    }));

    $dataset['reviews'] = $reviews;
    vd_save_dataset($dataset);
    return count($reviews) < $before;
}

function vd_clear_reviews() {
    $dataset = vd_get_dataset();
    $dataset['reviews'] = array();
    vd_save_dataset($dataset);
}

function vd_delete_order_by_id($id) {
    $dataset = vd_get_dataset();
    $orders = isset($dataset['orders']) ? $dataset['orders'] : array();
    $before = count($orders);

    $orders = array_values(array_filter($orders, function ($row) use ($id) {
        return (int) $row['id'] !== (int) $id;
    }));

    $dataset['orders'] = $orders;
    vd_save_dataset($dataset);
    return count($orders) < $before;
}

function vd_clear_orders() {
    $dataset = vd_get_dataset();
    $dataset['orders'] = array();
    vd_save_dataset($dataset);
}

function vd_clear_visitors() {
    $dataset = vd_get_dataset();
    $dataset['visitors'] = array();
    vd_save_dataset($dataset);
}

function vd_get_state_payload() {
    vd_boot_session();
    $snapshotOverride = vd_get_snapshot_override();
    if (!empty($_SESSION['virtual_data']['dataset']) && is_array($_SESSION['virtual_data']['dataset'])) {
        $snapshot = vd_get_dashboard_snapshot();
    } else {
        $snapshot = is_array($snapshotOverride) ? $snapshotOverride : array(
            'total_income' => '0.00',
            'today_income' => '0.00',
            'today_orders' => 0,
            'unreviewed_count' => 0,
            'today_visitors' => 0,
            'yesterday_visitors' => 0
        );
    }

    return array(
        'enabled' => vd_is_enabled(),
        'seed' => $_SESSION['virtual_data']['seed'],
        'generated_at' => $_SESSION['virtual_data']['generated_at'],
        'updated_at' => $_SESSION['virtual_data']['updated_at'],
        'snapshot_override_active' => is_array($snapshotOverride),
        'snapshot' => $snapshot
    );
}

function vd_get_dataset_template() {
    $now = date('Y-m-d H:i:s');
    $todayOrderNo = date('YmdHis') . '001';
    return array(
        'orders' => array(
            array(
                'id' => 1,
                'name' => '演示订单',
                'order_number' => $todayOrderNo,
                'ip_address' => '123.45.67.89',
                'ip_location' => '广东省 深圳市',
                'money' => '9.90',
                'payment_method' => 'wxpay',
                'payment_time' => $now,
                'payment_status' => '已支付'
            )
        ),
        'visitors' => array(
            array(
                'id' => 1,
                'ip_address' => '123.45.67.89',
                'ip_location' => '广东省 深圳市',
                'user_agent' => 'iPhone Safari',
                'visit_time' => $now,
                'page_url' => '/public/home_v1.php'
            )
        ),
        'reviews' => array(
            array(
                'id' => 1,
                'filename' => 'demo_1.jpg',
                'upload_time' => $now,
                'ip_address' => '123.45.67.89',
                'file_path' => '../upload/demo_1.jpg',
                'reviewer' => '管理员',
                'status' => 'pending',
                'ip_location' => '广东省 深圳市'
            )
        )
    );
}

function vd_get_dataset_for_edit() {
    vd_boot_session();
    if (!empty($_SESSION['virtual_data']['dataset']) && is_array($_SESSION['virtual_data']['dataset'])) {
        return $_SESSION['virtual_data']['dataset'];
    }
    return vd_get_dataset_template();
}

function vd_normalize_datetime($value) {
    $text = trim((string) $value);
    if ($text === '') {
        return date('Y-m-d H:i:s');
    }
    $timestamp = strtotime($text);
    if ($timestamp === false) {
        return date('Y-m-d H:i:s');
    }
    return date('Y-m-d H:i:s', $timestamp);
}

function vd_normalize_ip($value) {
    $text = trim((string) $value);
    if ($text !== '' && filter_var($text, FILTER_VALIDATE_IP)) {
        return $text;
    }
    return vd_make_ip();
}

function vd_normalize_dataset($incoming) {
    $dataset = is_array($incoming) ? $incoming : array();
    $orders = isset($dataset['orders']) && is_array($dataset['orders']) ? $dataset['orders'] : array();
    $visitors = isset($dataset['visitors']) && is_array($dataset['visitors']) ? $dataset['visitors'] : array();
    $reviews = isset($dataset['reviews']) && is_array($dataset['reviews']) ? $dataset['reviews'] : array();

    $normalizedOrders = array();
    foreach ($orders as $index => $row) {
        $item = is_array($row) ? $row : array();
        $paymentMethod = isset($item['payment_method']) ? (string) $item['payment_method'] : 'wxpay';
        if (!in_array($paymentMethod, array('wxpay', 'alipay'), true)) {
            $paymentMethod = 'wxpay';
        }
        $paymentStatus = isset($item['payment_status']) ? (string) $item['payment_status'] : '已支付';
        if (!in_array($paymentStatus, array('已支付', '未支付'), true)) {
            $paymentStatus = '已支付';
        }
        $money = isset($item['money']) ? (float) $item['money'] : 0;
        if ($money < 0) {
            $money = 0;
        }
        $paymentTime = vd_normalize_datetime($item['payment_time'] ?? '');
        $orderNumber = trim((string) ($item['order_number'] ?? ''));
        if ($orderNumber === '') {
            $orderNumber = date('YmdHis', strtotime($paymentTime)) . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
        }
        $normalizedOrders[] = array(
            'id' => isset($item['id']) ? (int) $item['id'] : ($index + 1),
            'name' => trim((string) ($item['name'] ?? '演示订单')),
            'order_number' => $orderNumber,
            'ip_address' => vd_normalize_ip($item['ip_address'] ?? ''),
            'ip_location' => trim((string) ($item['ip_location'] ?? '未知地区')),
            'money' => number_format($money, 2, '.', ''),
            'payment_method' => $paymentMethod,
            'payment_time' => $paymentTime,
            'payment_status' => $paymentStatus
        );
    }
    usort($normalizedOrders, function ($a, $b) {
        return strcmp($b['payment_time'], $a['payment_time']);
    });

    $normalizedVisitors = array();
    foreach ($visitors as $index => $row) {
        $item = is_array($row) ? $row : array();
        $normalizedVisitors[] = array(
            'id' => isset($item['id']) ? (int) $item['id'] : ($index + 1),
            'ip_address' => vd_normalize_ip($item['ip_address'] ?? ''),
            'ip_location' => trim((string) ($item['ip_location'] ?? '未知地区')),
            'user_agent' => trim((string) ($item['user_agent'] ?? 'Windows Chrome')),
            'visit_time' => vd_normalize_datetime($item['visit_time'] ?? ''),
            'page_url' => trim((string) ($item['page_url'] ?? '/public/home_v1.php'))
        );
    }
    usort($normalizedVisitors, function ($a, $b) {
        return strcmp($b['visit_time'], $a['visit_time']);
    });

    $normalizedReviews = array();
    foreach ($reviews as $index => $row) {
        $item = is_array($row) ? $row : array();
        $status = isset($item['status']) ? (string) $item['status'] : 'pending';
        if (!in_array($status, array('pending', 'approved', 'rejected'), true)) {
            $status = 'pending';
        }
        $filename = trim((string) ($item['filename'] ?? 'demo_1.jpg'));
        if ($filename === '') {
            $filename = 'demo_1.jpg';
        }
        $filePath = trim((string) ($item['file_path'] ?? ''));
        if ($filePath === '') {
            $filePath = '../upload/' . $filename;
        }
        $normalizedReviews[] = array(
            'id' => isset($item['id']) ? (int) $item['id'] : ($index + 1),
            'filename' => $filename,
            'upload_time' => vd_normalize_datetime($item['upload_time'] ?? ''),
            'ip_address' => vd_normalize_ip($item['ip_address'] ?? ''),
            'file_path' => $filePath,
            'reviewer' => trim((string) ($item['reviewer'] ?? '管理员')),
            'status' => $status,
            'ip_location' => trim((string) ($item['ip_location'] ?? '未知地区'))
        );
    }
    usort($normalizedReviews, function ($a, $b) {
        return strcmp($b['upload_time'], $a['upload_time']);
    });

    return array(
        'orders' => $normalizedOrders,
        'visitors' => $normalizedVisitors,
        'reviews' => $normalizedReviews
    );
}

function vd_set_custom_dataset($dataset, $enable = true) {
    vd_boot_session();
    $normalized = vd_normalize_dataset($dataset);
    $_SESSION['virtual_data']['dataset'] = $normalized;
    $_SESSION['virtual_data']['snapshot_override'] = null;
    $_SESSION['virtual_data']['seed'] = 'custom-' . substr(md5(vd_now_string() . mt_rand(1000, 9999)), 0, 10);
    $_SESSION['virtual_data']['generated_at'] = vd_now_string();
    $_SESSION['virtual_data']['updated_at'] = vd_now_string();
    $_SESSION['virtual_data']['enabled'] = $enable ? true : false;
}

function vd_get_dataset_json_pretty() {
    $dataset = vd_get_dataset_for_edit();
    return json_encode($dataset, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function vd_get_template_json_pretty() {
    return json_encode(vd_get_dataset_template(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function vd_save_dataset_from_json($jsonText, &$message) {
    $message = '';
    $raw = trim((string) $jsonText);
    if ($raw === '') {
        $message = 'JSON 内容不能为空';
        return false;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $message = 'JSON 格式不正确：' . json_last_error_msg();
        return false;
    }
    if (!isset($decoded['orders']) || !isset($decoded['visitors']) || !isset($decoded['reviews'])) {
        $message = 'JSON 顶层必须包含 orders、visitors、reviews 三个数组';
        return false;
    }
    vd_set_custom_dataset($decoded, true);
    $message = '自定义数据已保存并开启';
    return true;
}
