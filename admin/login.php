<?php
// 启动会话管理
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 检查错误信息
$error = $_SESSION['error'] ?? '';
if (!empty($error)) {
   
    // 清除错误信息
    $_SESSION['error'] = '';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <style>
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(to bottom, #2d59ff, #9ff9f9);
            color: #333;
        }

       .login-container {
            background-color: rgba(255, 255, 255, 0.9); /* 半透明白色背景，更炫酷 */
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2); /* 增强阴影效果 */
            padding: 40px;
            width: 90%;
            max-width: 400px;
            transition: all 0.3s ease; /* 添加过渡效果，让变化更平滑 */
            margin: 30px; /* 使登录框与屏幕边框保持30px距离 */
            box-sizing: border-box; /* 新增，确保内边距和边框计算在宽度内 */
        }

       .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2fbf63;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); /* 文字阴影，增强立体感 */
        }

       .login-container input[type="text"],
       .login-container input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: none;
            border-bottom: 1px solid #ccc; /* 底部边框样式，更简洁美观 */
            border-radius: 0; /* 去掉默认圆角，采用底部边框体现输入框 */
            background-color: transparent; /* 透明背景，融入整体 */
            outline: none; /* 去掉聚焦时的默认外框 */
            transition: border-bottom-color 0.3s ease; /* 过渡效果，改变输入框边框颜色时更平滑 */
            box-sizing: border-box; /* 确保输入框内边距和边框计算在宽度内 */
        }

       .login-container input[type="text"]:focus,
       .login-container input[type="password"]:focus {
            border-bottom-color: #4CAF50; /* 聚焦时改变边框颜色 */
        }

       .login-container button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to bottom, #9ff9f9, #2e82ff);
            color: white;
            border: none;
            border-radius: 30px; /* 更大的圆角，更美观 */
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease; /* 按钮背景色过渡效果 */
			margin-top: 20px;
			margin-bottom: 40px;
        }

       .login-container button:hover {
            background-color: #45a049;
        }

        /* 响应式设计，针对手机端进行调整 */
        @media (max-width: 768px) {
           .login-container {
                width: calc(100% - 40px); /* 手机端时也保持左右各20px距离 */
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>管理员登录</h2>
		<?php if (isset($error)): ?>
		        <p style="color: red;"><?php echo $error; ?></p>
		    <?php endif; ?>
        <form action="login_process.php" method="post">
            <input type="text" id="name" name="name" placeholder="用户名" required>
            <input type="password" id="password" name="password" placeholder="密码" required>
            <button type="submit">登 录</button>
        </form>
    </div>
</body>

</html>
