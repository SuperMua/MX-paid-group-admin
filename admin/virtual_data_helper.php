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
            'dataset' => null
        );
    }
}

function vd_now_string() {
    return date('Y-m-d H:i:s');
}

function vd_random_seed() {
    return (int) (microtime(true) * 1000) + mt_rand(1000, 9999);
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

function vd_generate_dataset($seed) {
    mt_srand((int) $seed);

    $orderNames = array('付费订单', '会员订单', '进群订单', '推广订单', 'VIP入群', '活动订单', '渠道订单', '加急订单');
    $locations = array('广东省 深圳市', '浙江省 杭州市', '江苏省 南京市', '四川省 成都市', '北京市 北京市', '上海市 上海市', '湖北省 武汉市', '福建省 厦门市', '陕西省 西安市', '河南省 郑州市');
    $agents = array('iPhone Safari', 'Android Chrome', 'Windows Chrome', 'Mac Safari', 'HarmonyOS Browser', 'Xiaomi Browser', 'Edge 126');
    $pages = array('/public/home_v1.php', '/public/home_v2.php', '/public/check.php', '/public/pay.php');
    $orderAmounts = array(9.90, 19.90, 29.90, 39.90, 49.90, 59.90, 99.00);
    $uploadNames = vd_pick_upload_filenames();

    $orders = array();
    $orderCount = mt_rand(72, 138);
    for ($i = 1; $i <= $orderCount; $i++) {
        $daysAgo = mt_rand(0, 14);
        $timestamp = strtotime('-' . $daysAgo . ' day');
        $timestamp = strtotime(date('Y-m-d', $timestamp) . ' ' . mt_rand(0, 23) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT));
        $paymentStatus = mt_rand(1, 100) <= 78 ? '已支付' : '未支付';
        $paymentMethod = mt_rand(0, 1) === 0 ? 'wxpay' : 'alipay';
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
    $visitorCount = mt_rand(180, 320);
    for ($i = 1; $i <= $visitorCount; $i++) {
        $daysAgo = mt_rand(0, 8);
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
    $reviewGroupCount = mt_rand(14, 28);
    for ($g = 1; $g <= $reviewGroupCount; $g++) {
        $ip = vd_make_ip();
        $imgCount = mt_rand(2, 6);
        for ($j = 0; $j < $imgCount; $j++) {
            $daysAgo = mt_rand(0, 12);
            $timestamp = strtotime('-' . $daysAgo . ' day');
            $timestamp = strtotime(date('Y-m-d', $timestamp) . ' ' . mt_rand(0, 23) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT));
            $randStatus = mt_rand(1, 100);
            if ($randStatus <= 40) {
                $status = 'pending';
            } elseif ($randStatus <= 82) {
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
                'reviewer' => '管理员',
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

function vd_regenerate_dataset() {
    vd_boot_session();
    $seed = vd_random_seed();
    $_SESSION['virtual_data']['seed'] = $seed;
    $_SESSION['virtual_data']['dataset'] = vd_generate_dataset($seed);
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
    if (!empty($_SESSION['virtual_data']['dataset']) && is_array($_SESSION['virtual_data']['dataset'])) {
        $snapshot = vd_get_dashboard_snapshot();
    } else {
        $snapshot = array(
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
