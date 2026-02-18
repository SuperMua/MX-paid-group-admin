<?php

// 启动会话管理
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    // 用户未登录，跳转到登录页面
    header("Location: login.php");
    exit;
}

// 用户已登录，显示首页内容

?>