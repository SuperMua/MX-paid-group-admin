<?php
// 引入数据库配置文件
require '../config/config.php';
// 查询 task_set 表的 SQL 语句
$sql = "SELECT * FROM task_set";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // 假设 settings 表中只有一条记录
    $row = $result->fetch_assoc();
    $title = $row['title'];
    $intro = $row['intro'];
    $requirement = $row['requirement'];
    $review_time = $row['review_time'];
	$download_img =  $row['download_img'];
    $example_img = $row['example_img'];
    $prompt = $row['prompt'];
}

// 查询 group_images 表中的 image_path 字段
$sql = "SELECT image_path FROM group_images";
$result = $conn->query($sql);

$group_images = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $group_images[] = $row['image_path'];
    }
}

// 查询 settings 表中的数据
$sql = "SELECT group_avatar, group_title, sub_title, entry_price, group_description, question, customer_service_image, warm_tip, qr_code, original_price, ordering, reviews1, reviews2, reviews3, reviews4, reviews5 FROM settings";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 假设 settings 表中只有一条记录
    $row = $result->fetch_assoc();
    $group_avatar = $row['group_avatar'];
    $group_title = $row['group_title'];
    $sub_title = $row['sub_title'];
    $entry_price = $row['entry_price'];
	$group_description =  $row['group_description'];
	$question =  $row['question'];
    $customer_service_image = $row['customer_service_image'];
    $warm_tip = $row['warm_tip'];
    $qr_code = $row['qr_code'];
	$ordering = $row['ordering'];
	$original_price = $row['original_price'];
	$reviews1 = $row['reviews1'];
	$reviews2 = $row['reviews2'];
	$reviews3 = $row['reviews3'];
	$reviews4 = $row['reviews4'];
	$reviews5 = $row['reviews5'];	
} else {
    // 如果没有查询到数据，可以根据需要进行处理，这里简单给变量赋空值
    $group_avatar = '';
    $group_title = '';
    $sub_title = '';
    $entry_price = 0;
    $customer_service_image = '';
	$group_description = '';
	$question = '';
    $warm_tip = '';
    $qr_code = '';
	$ordering = '';
	$reviews1 = '';
	$reviews2 = '';
	$reviews3 = '';
	$reviews4 = '';
	$reviews5 = '';
	
}

// 关闭数据库连接
$conn->close();
?>