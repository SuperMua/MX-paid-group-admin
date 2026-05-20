<?php
require_once 'login_check.php';
// 引入数据库配置文件
require_once __DIR__ . '/../config/config.php';

// 根据不同的操作类型进行处理
if (isset($_GET['action']) && $_GET['action'] === 'get_user_info') {
    // 查询当前用户信息，假设用户ID为1
    $sql = "SELECT name, avatar FROM admin WHERE id = 1";
    $result = $conn->query($sql);
	
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();	
        $response = [
            'username' => $row['name'],
            'avatar' => $row['avatar']
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['username' => '未找到用户', 'avatar' => '']);
    }
} elseif (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    // 获取表单数据
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];

    // 对密码进行 MD5 加密
    if (!empty($newPassword)) {
        $newPassword = md5($newPassword);
    }

    $avatarPath = null;
    // 处理头像上传
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $targetDir = "../static/images/";
        // 生成唯一文件名
        $fileName = '17' . sprintf("%06d", mt_rand(0, 999999));
        $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $targetFile = $targetDir . $fileName . '.' . $fileExtension;

        // 移动上传的文件
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatarPath = '../static/images/' . $fileName . '.' . $fileExtension;
        } else {
            echo "上传头像时出错";
            exit;
        }
    }

    // 构建更新SQL语句
    $updateFields = [];
    if (!empty($newUsername)) {
        $updateFields[] = "name = '$newUsername'";
    }
    if (!empty($newPassword)) {
        $updateFields[] = "password = '$newPassword'";
    }
    if ($avatarPath) {
        $updateFields[] = "avatar = '$avatarPath'";
    }

    if (!empty($updateFields)) {
        $updateSql = "UPDATE admin SET " . implode(', ', $updateFields) . " WHERE id = 1";
        if ($conn->query($updateSql) === TRUE) {
            echo "设置更新成功";
        } else {
            echo "更新数据库时出错: " . $conn->error;
        }
    } else {
        echo "没有需要更新的内容";
    }
}


// 定义图片文件夹路径
$uploadDir = '../upload';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['get_image_info'])) {
        // 获取图片数量和大小
        $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');
        $count = 0;
        $totalSize = 0;

        if ($handle = opendir($uploadDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $fileExtension = pathinfo($entry, PATHINFO_EXTENSION);
                    if (in_array(strtolower($fileExtension), $imageExtensions)) {
                        $count++;
                        $totalSize += filesize($uploadDir . '/' . $entry);
                    }
                }
            }
            closedir($handle);
        }

        // 将大小转换为合适的单位
        if ($totalSize >= 1024 * 1024) {
            $size = round($totalSize / (1024 * 1024), 2) . ' MB';
        } elseif ($totalSize >= 1024) {
            $size = round($totalSize / 1024, 2) . ' KB';
        } else {
            $size = $totalSize . ' B';
        }

        // 返回 JSON 数据
        header('Content-Type: application/json');
        echo json_encode(array(
            'count' => $count,
            'size' => $size
        ));
    } elseif (isset($_POST['clear_all'])) {
        // 清空文件夹中的图片
        $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');

        if ($handle = opendir($uploadDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $fileExtension = pathinfo($entry, PATHINFO_EXTENSION);
                    if (in_array(strtolower($fileExtension), $imageExtensions)) {
                        unlink($uploadDir . '/' . $entry);
                    }
                }
            }
            closedir($handle);
        } else {
            echo "无法打开文件夹";
            $conn->close();
            return;
        }

        // 清空 images 表的数据
        $sql = "DELETE FROM images";
        if ($conn->query($sql) === TRUE) {
            echo "操作成功";
        } else {
            echo "清空数据库表失败: " . $conn->error;
        }
    }
}



// 关闭数据库连接
$conn->close();
?>
