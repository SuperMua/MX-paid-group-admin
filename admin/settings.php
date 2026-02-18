<?php
require_once 'login_check.php';  //验证是否登陆
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置</title>
	<link rel="stylesheet" href="../static/css/admin.css">
    <style>
       .settings-headline {
		   display: none;
	   }

	   @media (min-width: 992px) {
		   .settings-headline {
			   display: block;
			   width: min(1200px, calc(100% - 64px));
			   margin: 76px auto 12px;
			   color: #667085;
			   font-size: 14px;
		   }
	   }
    </style>
</head>
<body>
<div class="navbar">
        <a class="back-button left-arrow" href="index.php"></a>
        <div class="title">系统设置</div>
    </div>
	<div class="settings-headline">请在下方选择要管理的系统配置模块</div>
	<div class="main" style="margin-top: 50px;">
		    <div class="sidebar">
				<div class="menu-item">
				    <a href="moban.php">
				        <span class="menu-item-icon icon_8"></span>模版设置</a>
				        <span class="menu-arrow" icon_8></span>
				    </a>
				</div>
				<div class="menu-item">
				    <a href="settings_frontend.php">
				        <span class="menu-item-icon icon_8"></span>任务设置</a>
				        <span class="menu-arrow"></span>
				    </a>
				</div>
				<div class="menu-item">
				    <a href="admin_settings.php">
				        <span class="menu-item-icon icon_8"></span>账号设置</a>
				        <span class="menu-arrow"></span>
				    </a>
				</div>
				<div class="menu-item">
				    <a href="payment_settings.php">
				        <span class="menu-item-icon icon_8"></span>支付设置</a>
				        <span class="menu-arrow"></span>
				    </a>
				</div>
				<div class="menu-item">
				    <a href="audit_status.php">
				        <span class="menu-item-icon icon_8"></span>审核设置</a>
				        <span class="menu-arrow"></span>
				    </a>
				</div>
				
		    </div>
</body>
</html>
