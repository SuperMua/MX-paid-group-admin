<?php
require_once 'login_check.php';
require_once __DIR__ . '/../config/config.php';
require_once 'virtual_data_helper.php';

$filename = 'review_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, array('记录ID', 'IP', '地区', '图片文件', '审核状态', '上传时间'));

$statusMap = array(
    'pending' => '待审核',
    'approved' => '已通过',
    'rejected' => '不通过'
);

if (vd_is_enabled()) {
    $rows = vd_get_dataset();
    $reviewRows = isset($rows['reviews']) ? $rows['reviews'] : array();
    foreach ($reviewRows as $row) {
        fputcsv($output, array(
            $row['id'],
            $row['ip_address'],
            $row['ip_location'],
            $row['filename'],
            $statusMap[$row['status']] ?? $row['status'],
            $row['upload_time']
        ));
    }
} else {
    $sql = "SELECT id, ip_address, ip_location, filename, status, upload_time FROM images ORDER BY upload_time DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, array(
                $row['id'],
                $row['ip_address'],
                $row['ip_location'],
                $row['filename'],
                $statusMap[$row['status']] ?? $row['status'],
                $row['upload_time']
            ));
        }
    }
}

fclose($output);
$conn->close();
exit;
?>
