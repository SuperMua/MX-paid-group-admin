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

	   .settings-sidebar .menu-item a {
		   display: grid;
		   grid-template-columns: 20px 1fr;
		   column-gap: 8px;
		   align-items: center;
	   }

	   .settings-sidebar .menu-item a .menu-title {
		   font-size: 15px;
		   grid-column: 2;
	   }

	   .settings-sidebar .menu-item .menu-item-icon {
		   grid-row: 1 / span 2;
	   }

	   .settings-sidebar .menu-item a .menu-desc {
		   display: none;
		   grid-column: 2;
	   }

	   @media (min-width: 992px) {
		   .settings-headline {
			   display: block;
			   width: min(1200px, calc(100% - 64px));
			   margin: 76px auto 12px;
			   color: #667085;
			   font-size: 14px;
		   }

		   .settings-main {
			   margin-top: 84px !important;
		   }

		   .settings-sidebar {
			   grid-template-columns: repeat(2, minmax(0, 1fr));
		   }

		   .settings-sidebar .menu-item a .menu-desc {
			   display: block;
			   font-size: 12px;
			   color: #98a2b3;
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
	<div class="main settings-main" style="margin-top: 50px;">
		    <div class="sidebar settings-sidebar">
				<div class="menu-item">
				    <a href="moban.php">
				        <span class="menu-item-icon icon_8"></span>
						<span class="menu-title">模版设置</span>
						<span class="menu-desc">配置前台模板、预览链接与二维码素材</span>
					</a>
				    <span class="menu-arrow" icon_8></span>
				</div>
				<div class="menu-item">
				    <a href="settings_frontend.php">
				        <span class="menu-item-icon icon_8"></span>
						<span class="menu-title">任务设置</span>
						<span class="menu-desc">配置任务文案、审核提示与示例图片</span>
					</a>
				    <span class="menu-arrow"></span>
				</div>
				<div class="menu-item">
				    <a href="admin_settings.php">
				        <span class="menu-item-icon icon_8"></span>
						<span class="menu-title">账号设置</span>
						<span class="menu-desc">管理后台昵称、密码与头像</span>
					</a>
				    <span class="menu-arrow"></span>
				</div>
				<div class="menu-item">
				    <a href="payment_settings.php">
				        <span class="menu-item-icon icon_8"></span>
						<span class="menu-title">支付设置</span>
						<span class="menu-desc">配置支付接口、商户参数与回调地址</span>
					</a>
				    <span class="menu-arrow"></span>
				</div>
				<div class="menu-item">
				    <a href="audit_status.php">
				        <span class="menu-item-icon icon_8"></span>
						<span class="menu-title">审核设置</span>
						<span class="menu-desc">配置自动审核开关与风险提示</span>
					</a>
				    <span class="menu-arrow"></span>
				</div>
				
		    </div>
</body>
</html>
