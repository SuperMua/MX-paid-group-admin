<?php
// 引入数据库配置文件
require_once __DIR__ . '/../config/config.php';

// 启动会话管理
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';

    // 防止SQL注入
    $name = mysqli_real_escape_string($conn, $name);
    $password = mysqli_real_escape_string($conn, $password);

    // 对用户输入的密码进行MD5加密
    $passwordMd5 = md5($password);

    // 查询用户信息
    $sql = "SELECT * FROM admin WHERE name = ? AND password = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // 预处理语句创建失败，显示错误信息
        $_SESSION['error'] = '查询准备失败: ' . $conn->error;
        header("Location: login.php");
        exit;
    }

    $stmt->bind_param("ss", $name, $passwordMd5);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 用户存在，设置会话变量
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];

        // 跳转到首页
        header("Location: index.php");
        exit;
    } else {
        // 用户不存在，显示错误信息
        $_SESSION['error'] = "用户名或密码错误";
        header("Location: login.php");
        exit;
    }

    // 关闭语句
    $stmt->close();
}

// 关闭数据库连接
$conn->close();

?>