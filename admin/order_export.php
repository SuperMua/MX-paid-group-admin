<?php
require_once 'login_check.php';
require_once '../config/config.php';
require_once 'virtual_data_helper.php';

$paymentMap = array(
    'wxpay' => '微信',
    'alipay' => '支付宝'
);

$filename = 'orders_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, array('订单号', '名称', 'IP', '地区', '金额', '支付方式', '支付状态', '支付时间'));

if (vd_is_enabled()) {
    $rows = vd_get_orders_all();
    foreach ($rows as $row) {
        fputcsv($output, array(
            $row['order_number'],
            $row['name'],
            $row['ip_address'],
            $row['ip_location'],
            $row['money'],
            $paymentMap[$row['payment_method']] ?? $row['payment_method'],
            $row['payment_status'],
            $row['payment_time']
        ));
    }
} else {
    $sql = "SELECT order_number, name, ip_address, ip_location, money, payment_method, payment_status, payment_time FROM orders ORDER BY payment_time DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, array(
                $row['order_number'],
                $row['name'],
                $row['ip_address'],
                $row['ip_location'],
                $row['money'],
                $paymentMap[$row['payment_method']] ?? $row['payment_method'],
                $row['payment_status'],
                $row['payment_time']
            ));
        }
    }
}

fclose($output);
$conn->close();
exit;
?>
