<?php 
require 'auto_status.php';
require_once 'login_check.php'; 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../static/css/admin.css">
    <title>审核设置</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            margin: 0;
            background-color: #fafbfc;
        }

       .container {
		   margin: 50px 10px 0px 10px;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(16, 24, 40, 0.05);
			width: 90%;
        }

       .settings-intro {
            margin: 0 0 14px;
            color: #667085;
            font-size: 13px;
            line-height: 1.6;
        }

       .audit-switch-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 14px;
            border: 1px solid #e9edf3;
            border-radius: 10px;
            background: #f8fafc;
        }

       .switch-text {
            font-size: 14px;
            color: #344054;
        }

       .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

       .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

       .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 34px;
        }

       .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked +.slider {
            background-color: #2196F3;
        }

        input:focus +.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked +.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        #statusText {
            margin-top: 14px;
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            border: 1px solid #d0d5dd;
            background: #f2f4f7;
            color: #475467;
        }

        #statusText.status-on {
            border-color: #b7eb8f;
            background: #f6ffed;
            color: #389e0d;
        }

        #statusText.status-off {
            border-color: #ffd8bf;
            background: #fff7e6;
            color: #d46b08;
        }
		.statusfxts{
			font-size: 13px;
			color: #98a2b3;
			margin-top: 14px;
            line-height: 1.7;
		}

		@media (min-width: 992px) {
			.container {
				width: min(980px, calc(100% - 64px));
				margin: 76px auto 24px;
				padding: 24px 28px;
			}
		}
    </style>
</head>

<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">审核设置</div>
    </div>
    <div class="container">
        <h4>开启自动审核</h4>
        <p class="settings-intro">开启后系统将自动审核提交内容，请谨慎评估业务风险并定期抽检审核结果。</p>
        <div class="audit-switch-row">
            <span class="switch-text">自动审核开关</span>
            <label class="switch">
                <input type="checkbox" id="toggleSwitch" <?php echo $isAutoAuditEnabled? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
        </div>
        <div id="statusText"><?php echo $isAutoAuditEnabled? '自动审核已开启' : '自动审核已关闭'; ?></div>
		<div class="statusfxts">注意：自动审核不会理解图文语义，建议仅用于低风险场景，并配合人工抽检机制。</div>
    </div>
    <script>
        const toggleSwitch = document.getElementById('toggleSwitch');
        const statusText = document.getElementById('statusText');

        function syncStatusClass(isEnabled) {
            statusText.classList.toggle('status-on', isEnabled);
            statusText.classList.toggle('status-off', !isEnabled);
        }

        function toggleAutoAudit() {
            const newStatus = toggleSwitch.checked? 1 : 0;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'audit_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        statusText.textContent = newStatus? '自动审核已开启': '自动审核已关闭';
                        syncStatusClass(newStatus === 1);
                    } else {
                        console.error('更新状态失败:', response.message);
                        // 恢复开关状态
                        toggleSwitch.checked =!toggleSwitch.checked;
                        syncStatusClass(toggleSwitch.checked);
                    }
                }
            };
            xhr.send('status=' + newStatus);
        }

        syncStatusClass(toggleSwitch.checked);
        toggleSwitch.addEventListener('change', toggleAutoAudit);
    </script>
</body>

</html>

<?php
// 关闭数据库连接
$conn->close();
?>
