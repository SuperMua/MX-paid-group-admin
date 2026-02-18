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
        :root {
            --login-primary: #1677ff;
            --login-primary-hover: #0f63d4;
            --login-card-radius: 16px;
            --login-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
            --login-border: rgba(255, 255, 255, 0.22);
            --login-text: #1f2937;
            --login-sub: #6b7280;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            color: var(--login-text);
            background:
                radial-gradient(circle at 18% 22%, rgba(255, 255, 255, 0.26) 0, rgba(255, 255, 255, 0) 42%),
                radial-gradient(circle at 80% 14%, rgba(187, 247, 255, 0.32) 0, rgba(187, 247, 255, 0) 40%),
                linear-gradient(135deg, #0d47a1 0%, #1565c0 34%, #1d4ed8 62%, #60a5fa 100%);
            padding: 24px;
        }

        .login-container {
            width: min(100%, 460px);
            padding: 34px 32px;
            border-radius: var(--login-card-radius);
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--login-border);
            backdrop-filter: blur(6px);
            box-shadow: var(--login-shadow);
        }

        .login-container h2 {
            margin: 0 0 8px;
            text-align: center;
            color: #0f172a;
            font-size: 28px;
            letter-spacing: 1px;
        }

        .login-subtitle {
            margin: 0 0 26px;
            text-align: center;
            font-size: 13px;
            color: var(--login-sub);
        }

        .login-error {
            margin: 0 0 12px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #fff1f0;
            border: 1px solid #ffccc7;
            color: #cf1322;
            font-size: 13px;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            border: 1px solid #d0d5dd;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 12px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background: #fff;
            color: #111827;
        }

        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: var(--login-primary);
            box-shadow: 0 0 0 3px rgba(22, 119, 255, 0.15);
        }

        .login-container button {
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 6px;
            font-size: 15px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(135deg, #1677ff 0%, #3b82f6 100%);
            cursor: pointer;
            transition: transform 0.15s ease, background-color 0.2s ease;
        }

        .login-container button:hover {
            background: linear-gradient(135deg, var(--login-primary-hover) 0%, #2563eb 100%);
            transform: translateY(-1px);
        }

        @media (min-width: 992px) {
            .login-container {
                width: min(100%, 520px);
                padding: 40px 40px;
            }

            .login-container h2 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>管理员登录</h2>
        <p class="login-subtitle">请输入管理员账号和密码后继续</p>
		<?php if (!empty($error)): ?>
		        <p class="login-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
		    <?php endif; ?>
        <form action="login_process.php" method="post">
            <input type="text" id="name" name="name" placeholder="用户名" required>
            <input type="password" id="password" name="password" placeholder="密码" required>
            <button type="submit">登 录</button>
        </form>
    </div>
</body>

</html>
