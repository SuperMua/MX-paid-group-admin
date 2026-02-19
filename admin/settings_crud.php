<?php
require_once 'login_check.php';
require_once '../config/config.php';

// 处理获取设置数据的请求
if (isset($_GET['action']) && $_GET['action'] === 'getSettings') {
    $id = $_GET['id'];
    $sql = "SELECT * FROM settings WHERE id = $id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $settings = $row;


        // 获取群图介绍图片
        $sql = "SELECT image_path FROM group_images WHERE group_id = $id";
        $imageResult = $conn->query($sql);
        $settings['group_images'] = [];
        while ($imageRow = $imageResult->fetch_assoc()) {
            $settings['group_images'][] = $imageRow['image_path'];
        }


        // 将数据以 JSON 格式输出
        header('Content-Type: application/json');
        echo json_encode($settings);
    } else {
        // 未找到设置数据
        header('Content-Type: application/json');
        echo json_encode([]);
    }
    $conn->close();
    exit;
}


// 处理更新群设置的请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $id = $_POST['id'];
    $group_title = $_POST['group_title'];
    $sub_title = $_POST['sub_title'];
    $entry_price = $_POST['entry_price'];
    $group_description = $_POST['group_description'];
    $question = $_POST['question'];
    $warm_tip = $_POST['warm_tip'];
	$ordering = $_POST['ordering'];
	$original_price = $_POST['original_price'];
	$reviews1 = $_POST['reviews1'];
	$reviews2 = $_POST['reviews2'];
	$reviews3 = $_POST['reviews3'];
	$reviews4 = $_POST['reviews4'];
	$reviews5 = $_POST['reviews5'];
	


    // 处理群头像上传
    if (isset($_FILES['group_avatar']) && $_FILES['group_avatar']['error'] === UPLOAD_ERR_OK) {
        $group_avatar = '../static/images/'.uniqid().'_'.$_FILES['group_avatar']['name'];
        move_uploaded_file($_FILES['group_avatar']['tmp_name'], $group_avatar);
    } else {
        $sql = "SELECT group_avatar FROM settings WHERE id = $id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $group_avatar = $row['group_avatar'];
        } else {
            $group_avatar = '';
        }
    }


    // 处理客服二维码上传
    if (isset($_FILES['customer_service_image']) && $_FILES['customer_service_image']['error'] === UPLOAD_ERR_OK) {
        $customer_service_image = '../static/images/'.uniqid().'_'.$_FILES['customer_service_image']['name'];
        move_uploaded_file($_FILES['customer_service_image']['tmp_name'], $customer_service_image);
    } else {
        $sql = "SELECT customer_service_image FROM settings WHERE id = $id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $customer_service_image = $row['customer_service_image'];
        } else {
            $customer_service_image = '';
        }
    }


    // 处理入群二维码上传
    if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
        $qr_code = '../static/images/'.uniqid().'_'.$_FILES['qr_code']['name'];
        move_uploaded_file($_FILES['qr_code']['tmp_name'], $qr_code);
    } else {
        $sql = "SELECT qr_code FROM settings WHERE id = $id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $qr_code = $row['qr_code'];
        } else {
            $qr_code = '';
        }
    }


    // 生成 SQL 语句
    $sql = "UPDATE settings SET
            group_title = '$group_title', 
            sub_title = '$sub_title', 
            entry_price = '$entry_price', 
            group_description = '$group_description', 
            question = '$question',
            group_avatar = '$group_avatar', 
            customer_service_image = '$customer_service_image', 
            qr_code = '$qr_code', 
            warm_tip = '$warm_tip',
            ordering = '$ordering',
			original_price = '$original_price',
			reviews1 = '$reviews1',
			reviews2 = '$reviews2',
			reviews3 = '$reviews3',
			reviews4 = '$reviews4',
			reviews5 = '$reviews5'
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        // 处理群图介绍图片的添加和删除
        if (isset($_FILES['group_images']) && $_FILES['group_images']['error'][0] === UPLOAD_ERR_OK) {
            // 处理群图介绍图片的上传
            foreach ($_FILES['group_images']['name'] as $key => $name) {
                 // 生成 5 位纯数字的文件名
                $newFileName = '17'.sprintf("%06d", rand(0, 99999)).'.'.pathinfo($name, PATHINFO_EXTENSION);
                $image_path = '../static/images/'.$newFileName;
                move_uploaded_file($_FILES['group_images']['tmp_name'][$key], $image_path);
                $sql = "INSERT INTO group_images (group_id, image_path) VALUES ($id, '$image_path')";
                $conn->query($sql);
            }
        }


        if (isset($_POST['remove_images'])) {
            $remove_images = $_POST['remove_images'];
            foreach ($remove_images as $remove_image) {
                // 确保 remove_image 是准确的文件路径
                $remove_image = basename($remove_image); 
                // 修改：使用 LIKE 关键字，以处理路径中可能的细微差异
                $sql = "DELETE FROM group_images WHERE image_path LIKE '%$remove_image' AND group_id = $id";
                $conn->query($sql);
            }
        }


        header("Location: settings_page.php?success=true");
    } else {
        echo "Error: ". $sql. "<br>". $conn->error;
    }
}


// 关闭数据库连接
$conn->close();
?>
