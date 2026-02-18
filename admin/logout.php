<?php
//退出登陆操作
// 启动会话管理
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 销毁会话
session_destroy();

// 重定向到登录页面
header("Location: login.php");
exit;
?>