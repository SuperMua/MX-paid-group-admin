<?php
// 开启会话
//session_start();

// 上传图片处理
// 连接数据库
require __DIR__ . '/../config/config.php';

// 获取客户端 IP 地址
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
    return $ipaddress;
}

function get_ip_location($ip) {
    // 过滤内网和无效IP
    if ($ip == 'UNKNOWN' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return '内网/无效IP';
    }

    $apiUrl = "https://api.vore.top/api/IPdata?ip=" . urlencode($ip);
    
    // 增强请求可靠性
    $options = [
        'http' => [
            'timeout' => 2, // 2秒超时
            'ignore_errors' => true // 忽略HTTP错误码
        ]
    ];
    $context = stream_context_create($options);
    
    $response = @file_get_contents($apiUrl, false, $context);
    if (!$response) return '未知';

    $data = json_decode($response, true);
    
    // 解析Vore-TOP响应结构
    if (isset($data['ipdata']['info1'], $data['ipdata']['info2'])) {
        return $data['ipdata']['info1'] . ' ' . $data['ipdata']['info2'];
    }
    
    return '未知';
}

// 检查是否有文件上传
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    $files = $_FILES['images'];
    $ipAddress = get_client_ip();
    $ipLocation = get_ip_location($ipAddress); // 获取 IP 地址归属地
    $uploadDir = __DIR__. '/../upload/'; // 使用上一层目录的绝对路径

    // 确保目录存在
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadResults = [];

    // 遍历上传的文件
    foreach ($files['name'] as $key => $value) {
        $originalFileName = basename($files['name'][$key]);
        $fileTmpName = $files['tmp_name'][$key];
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $newFileName = time(). rand(1000, 9999). '.'. $fileExtension; // 生成纯数字文件名
        $fileDestination = $uploadDir. $newFileName;
        $relativeFilePath = 'upload/'. $newFileName; // 相对路径

        // 移动文件到指定目录
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            // 插入图片信息到数据库
            $stmt = $conn->prepare("INSERT INTO images (filename, upload_time, ip_address, ip_location, file_path, status) VALUES (?, NOW(),?,?,?, 'pending')");
            if ($stmt) {
                $stmt->bind_param("ssss", $newFileName, $ipAddress, $ipLocation, $relativeFilePath);
                if ($stmt->execute()) {
                    // 记录上传结果
                    $uploadResults[] = [
                        'filename' => $newFileName,
                        'upload_time' => date('Y-m-d H:i:s'),
                        'file_path' => $relativeFilePath,
                        'status' => 'pending'
                    ];
                } else {
                    $uploadResults[] = [
                        'filename' => $newFileName,
                        'error' => "数据库插入失败：". $stmt->error
                    ];
                }
                $stmt->close();
            } else {
                $uploadResults[] = [
                    'filename' => $newFileName,
                    'error' => "准备语句失败：". $conn->error
                ];
            }
        } else {
            $uploadResults[] = [
                'filename' => $newFileName,
                'error' => "文件移动失败，临时文件：". $fileTmpName. "，目标路径：". $fileDestination. "，错误信息：". error_get_last()['message']
            ];
        }
    }

    if (count($uploadResults) > 0) {
        echo json_encode(['success' => true, 'results' => $uploadResults]);
    } else {
        echo json_encode(['success' => false, 'error' => '没有文件上传或全部文件上传失败']);
    }
} else {
    echo json_encode(['success' => false, 'error' => '没有文件上传！']);
}


// 关闭数据库连接
$conn->close();
?>