<?php
// 引入数据库配置文件
require __DIR__ . '/../config/config.php';

// 检查连接是否成功
if ($conn->connect_error) {
    // 如果连接失败，以 JSON 格式返回错误信息
    header('Content-Type: application/json');
    echo json_encode(['error' => '数据库连接失败: '. $conn->connect_error]);
    exit();
}

// 编写 SQL 查询语句
$sql = "SELECT prompt FROM task_set";
// 执行查询
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // 获取查询结果的第一行数据（假设只有一行数据，或者只取第一行）
    $row = $result->fetch_assoc();
    // 以 JSON 格式返回 prompt 字段的值
    header('Content-Type: application/json');
    echo json_encode(['prompt' => $row['prompt']]);
} else {
    // 如果没有找到数据，以 JSON 格式返回错误信息
    header('Content-Type: application/json');
    echo json_encode(['error' => '未找到对应的 prompt 数据']);
}

// 关闭数据库连接
$conn->close();
?>