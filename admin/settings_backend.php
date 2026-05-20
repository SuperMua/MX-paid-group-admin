<?php
require_once 'login_check.php';
// 引入数据库配置文件
require_once __DIR__ . '/../config/config.php';

// 根据不同的操作类型进行处理
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    // 获取现有任务设置数据
    $sql = "SELECT * FROM task_set";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode([]);
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 更新任务设置数据
    $title = $conn->real_escape_string($_POST['title']);
    $intro = $conn->real_escape_string($_POST['intro']);
    $requirement = $conn->real_escape_string($_POST['requirement']);
    $review_time = $conn->real_escape_string($_POST['review_time']);
    $prompt = $conn->real_escape_string($_POST['prompt']);

    $download_img = '';
    $example_img = '';

    // 处理下载图片上传
    if (isset($_FILES['download_img']) && $_FILES['download_img']['error'] === 0) {
        $uploadDir = '../static/images/';
        $extension = pathinfo($_FILES['download_img']['name'], PATHINFO_EXTENSION);
        // 生成以 17 开头的 8 位随机文件名
        $randomNumber = mt_rand(0, 999999);
        $newFileName = '17'. str_pad($randomNumber, 6, '0', STR_PAD_LEFT). '.' . $extension;
        $uploadPath = $uploadDir. $newFileName;

        if (move_uploaded_file($_FILES['download_img']['tmp_name'], $uploadPath)) {
            $download_img = $uploadPath;
        } else {
            $response = array('status' => 'error', 'message' => '下载图片上传失败');
            echo json_encode($response);
            $conn->close();
            exit;
        }
    }

    // 处理示例图上传
    if (isset($_FILES['example_img']) && $_FILES['example_img']['error'] === 0) {
        $uploadDir = '../static/images/';
        $extension = pathinfo($_FILES['example_img']['name'], PATHINFO_EXTENSION);
        // 生成以 17 开头的 8 位随机文件名
        $randomNumber = mt_rand(0, 999999);
        $newFileName = '17'. str_pad($randomNumber, 6, '0', STR_PAD_LEFT). '.' . $extension;
        $uploadPath = $uploadDir. $newFileName;

        if (move_uploaded_file($_FILES['example_img']['tmp_name'], $uploadPath)) {
            $example_img = $uploadPath;
        } else {
            $response = array('status' => 'error', 'message' => '示例图上传失败');
            echo json_encode($response);
            $conn->close();
            exit;
        }
    }

    // 获取当前记录的图片路径
    $currentDataSql = "SELECT download_img, example_img FROM task_set WHERE id = 1";
    $currentDataResult = $conn->query($currentDataSql);
    $currentData = $currentDataResult->fetch_assoc();

    // 如果没有新图片上传，使用原来的图片路径
    if (empty($download_img)) {
        $download_img = $currentData['download_img'];
    }
    if (empty($example_img)) {
        $example_img = $currentData['example_img'];
    }

    $sql = "UPDATE task_set
            SET title = '$title',
                intro = '$intro',
                requirement = '$requirement',
                review_time = '$review_time',
                download_img = '$download_img',
                example_img = '$example_img',
                prompt = '$prompt'
            WHERE id = 1"; // 假设表中只有一条记录，id 为 1

    if ($conn->query($sql) === TRUE) {
        $response = array('status' => 'success', 'message' => '更新成功');
        echo json_encode($response);
    } else {
        $response = array('status' => 'error', 'message' => '更新失败: '. $conn->error);
        echo json_encode($response);
    }
}


// 关闭数据库连接
$conn->close();
?>
