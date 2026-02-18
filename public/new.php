<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once 'settings_data.php'; 
//开启会话
session_start();

// 检查是否有令牌且令牌是否匹配
if (!isset($_GET['token']) || strlen($_GET['token'])!== 64 || $_GET['token']!== $_SESSION['image_token']) {
    http_response_code(403); // 禁止访问
    exit;
}

// 提供图像
$imagePath = $qr_code;  // 可以修改为不同的图片路径，支持多种格式，如.jpg,.gif 等
$imageInfo = getimagesize($imagePath);
if ($imageInfo === false) {
    // 处理文件读取失败的情况
    http_response_code(500);
    exit('Error reading image file.');
}

// 获取图像的 MIME 类型
$imageType = $imageInfo['mime'];
header("Content-Type: ". $imageType);

// 读取图像数据
$imageData = file_get_contents($imagePath);
if ($imageData === false) {
    // 处理文件读取失败的情况
    http_response_code(500);
    exit('Error reading image file.');
}

echo $imageData;
?>