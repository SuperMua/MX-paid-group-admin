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
            margin-top: 20px;
        }
		.statusfxts{
			font-size: 14px;
			color: #ccc;
			margin-top: 20px;
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
        <a class="back-button left-arrow" href="#" onclick="history.length>1 ? history.back() : location.href=document.referrer||'/';"></a>
        <div class="title">审核设置</div>
    </div>
    <div class="container">
        <h4>开启自动审核</h4>
        <label class="switch">
            <input type="checkbox" id="toggleSwitch" <?php echo $isAutoAuditEnabled? 'checked' : ''; ?>>
            <span class="slider"></span>
        </label>
        <div id="statusText"><?php echo $isAutoAuditEnabled? '自动审核已开启' : '自动审核已关闭'; ?></div>
		<div class="statusfxts">注意:开启自动审核,系统并不会识别内容,请自行承担风险</div>
    </div>
    <script>
        const toggleSwitch = document.getElementById('toggleSwitch');
        const statusText = document.getElementById('statusText');

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
                    } else {
                        console.error('更新状态失败:', response.message);
                        // 恢复开关状态
                        toggleSwitch.checked =!toggleSwitch.checked;
                    }
                }
            };
            xhr.send('status=' + newStatus);
        }

        toggleSwitch.addEventListener('change', toggleAutoAudit);
    </script>
</body>

</html>

<?php
// 关闭数据库连接
$conn->close();
?>
