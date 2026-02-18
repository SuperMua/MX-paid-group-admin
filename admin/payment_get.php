<?php
// 连接数据库
require '../config/config.php';

// 根据不同的请求类型处理操作
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 查询支付设置数据，包含新的 callback_url 字段
    $sql = "SELECT api_url, merchant_id, secre_key, callback_url FROM payment LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['api_url' => '', 'merchant_id' => '', 'secre_key' => '', 'callback_url' => '']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据，包含新的 callback_url 字段
    $apiUrl = $_POST['api_url'];
    $merchantId = $_POST['merchant_id'];
    $secreKey = $_POST['secre_key'];
    $callbackUrl = $_POST['callback_url'];

    // 防止 SQL 注入，对输入数据进行转义
    $apiUrl = $conn->real_escape_string($apiUrl);
    $merchantId = $conn->real_escape_string($merchantId);
    $secreKey = $conn->real_escape_string($secreKey);
    $callbackUrl = $conn->real_escape_string($callbackUrl);

    // 更新支付设置数据，包含新的 callback_url 字段
    $sql = "UPDATE payment SET api_url = '$apiUrl', merchant_id = '$merchantId', secre_key = '$secreKey', callback_url = '$callbackUrl' WHERE id = 1";

    if ($conn->query($sql) === TRUE) {
        echo "更新成功";
    } else {
        echo "更新失败: ". $conn->error;
    }
}

// 关闭数据库连接
$conn->close();
?>