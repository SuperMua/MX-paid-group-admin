<?php
require_once 'login_check.php';
require_once __DIR__ . '/../config/config.php';
require_once 'virtual_data_helper.php';

$filename = 'visitors_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, array('IP', '地区', '设备信息', '访问页面', '访问时间'));

if (vd_is_enabled()) {
    $rows = vd_get_dataset();
    $visitorRows = isset($rows['visitors']) ? $rows['visitors'] : array();
    foreach ($visitorRows as $row) {
        fputcsv($output, array(
            $row['ip_address'],
            $row['ip_location'],
            $row['user_agent'],
            $row['page_url'],
            $row['visit_time']
        ));
    }
} else {
    $sql = "SELECT ip_address, ip_location, user_agent, page_url, visit_time FROM visitors ORDER BY visit_time DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, array(
                $row['ip_address'],
                $row['ip_location'],
                $row['user_agent'],
                $row['page_url'],
                $row['visit_time']
            ));
        }
    }
}

fclose($output);
$conn->close();
exit;
?>
