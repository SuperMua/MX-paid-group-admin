<?php

// 启动会话管理
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    // 用户未登录，跳转到当前 admin 目录下的登录页（兼容 /admin 无尾斜杠访问）
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $scriptDir = $scriptName !== '' ? dirname($scriptName) : '/admin';
    if ($scriptDir === '\\' || $scriptDir === '/' || $scriptDir === '.') {
        $scriptDir = '/admin';
    }
    header('Location: ' . rtrim($scriptDir, '/') . '/login.php');
    exit;
}

// 用户已登录，显示首页内容

?>
