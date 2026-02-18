<?php 
require_once 'login_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../static/css/admin.css">
    <title>支付设置</title>
    <style>
       body {
           margin: 0;
           background:
               radial-gradient(1100px 450px at 100% 0%, rgba(245, 57, 248, 0.1), transparent 50%),
               radial-gradient(1000px 380px at 0% 0%, rgba(83, 86, 251, 0.16), transparent 55%),
               #f5f7ff;
       }

       .settings-container {
           width: min(1080px, calc(100% - 24px));
           margin: 68px auto 24px;
           border: 1px solid rgba(83, 86, 251, 0.16);
           border-radius: 18px;
           background: #fff;
           box-shadow: 0 18px 34px rgba(83, 86, 251, 0.12);
           padding: 20px;
       }

       .section-title {
           margin: 0 0 8px;
           color: #1f2a47;
           font-size: 22px;
       }

       .settings-intro {
           margin: 0 0 16px;
           font-size: 13px;
           color: #667085;
           line-height: 1.7;
       }

       .input-group {
           margin-bottom: 14px;
       }

       .input-group label {
           display: block;
           margin-bottom: 7px;
           font-size: 14px;
           color: #4a567a;
           font-weight: 600;
       }

       .input-group input {
           width: 100%;
           padding: 11px 12px;
           border: 1px solid #d8d6ff;
           border-radius: 12px;
           font-size: 14px;
           outline: none;
           transition: border-color 0.2s ease, box-shadow 0.2s ease;
       }

       .input-group input:focus {
           border-color: #5356fb;
           box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.14);
       }

       .input-tip {
           margin: 7px 0 0;
           font-size: 12px;
           color: #98a2b3;
       }

       .settings-save-wrap {
           margin-top: 20px;
           display: flex;
           justify-content: flex-end;
       }

       .settings-save-btn {
           min-width: 200px;
           border: none;
           border-radius: 999px;
           padding: 12px 20px;
           color: #fff;
           font-weight: 700;
           cursor: pointer;
           background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
           box-shadow: 0 14px 28px rgba(83, 86, 251, 0.26);
       }

       .input-xt {
           margin: 14px 0 0;
           padding: 8px 12px;
           border-radius: 999px;
           font-size: 12px;
           color: #667085;
           background: #f1f3ff;
           display: inline-flex;
       }

       .custom-popup {
           position: fixed;
           top: 50%;
           left: 50%;
           transform: translate(-50%, -50%);
           display: none;
           padding: 10px 18px;
           border-radius: 999px;
           background: rgba(39, 45, 75, 0.88);
           color: #fff;
           font-size: 13px;
           z-index: 1000;
       }

       @media (min-width: 992px) {
           .settings-container {
               margin-top: 78px;
               padding: 24px 28px;
           }
       }
    </style>
</head>

<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">支付设置</div>
    </div>
    <div class="settings-container">
	   <h2 class="section-title">支付网关参数</h2>
	   <p class="settings-intro">请填写支付平台参数，保存后立即生效；建议优先在测试环境验证回调链路。</p>
        <div class="input-group">
            <label for="api_url">支付地址api</label>
            <input type="url" id="api_url" placeholder="例如：https://pay.example.com/" spellcheck="false">
            <p class="input-tip">示例：https://你的支付域名/ ，末尾建议保留 / 。</p>
        </div>
        <div class="input-group">
            <label for="merchant_id">商户id</label>
            <input type="text" id="merchant_id" placeholder="请输入商户 ID（数字或字母）" spellcheck="false">
        </div>
        <div class="input-group">
            <label for="secre_key">商户秘钥 Key</label>
            <input type="text" id="secre_key" placeholder="请输入商户密钥 Key" spellcheck="false">
        </div>
		<div class="input-group">
		    <label for="callback_url">回调地址</label>
		    <input type="url" id="callback_url" placeholder="例如：https://example.com/pay/notify_url.php" spellcheck="false">
            <p class="input-tip">请确保公网可访问，且回调地址与支付平台后台配置保持一致。</p>
		</div>
        <div class="settings-save-wrap">
            <button class="settings-save-btn" onclick="updatePaymentSettings()">保存支付设置</button>
        </div>
		<p class="input-xt">本系统已对接易支付</p>
    </div>
    <div class="custom-popup" id="popup">更新成功</div>

   <script>
           document.addEventListener('DOMContentLoaded', function () {
               // 页面加载时读取数据
               fetch('payment_get.php')
                 .then(response => response.json())
                 .then(data => {
                       document.getElementById('api_url').value = data.api_url;
                       document.getElementById('merchant_id').value = data.merchant_id;
                       document.getElementById('secre_key').value = data.secre_key;
					   document.getElementById('callback_url').value = data.callback_url;
                   })
                 .catch(error => {
                       console.error('获取支付设置信息出错:', error);
                   });
           });
   
           function updatePaymentSettings() {
               const apiUrl = document.getElementById('api_url').value;
               const merchantId = document.getElementById('merchant_id').value;
               const secreKey = document.getElementById('secre_key').value;
			   const callbackUrl = document.getElementById('callback_url').value;
   
               const formData = new FormData();
               formData.append('api_url', apiUrl);
               formData.append('merchant_id', merchantId);
               formData.append('secre_key', secreKey);
			   formData.append('callback_url', callbackUrl);
   
               fetch('payment_get.php', {
                   method: 'POST',
                   body: formData
               })
                 .then(response => response.text())
                 .then(data => {
                       if (data === '更新成功') {
                           const popup = document.getElementById('popup');
                           popup.style.display = 'block';
                           setTimeout(() => {
                               popup.style.display = 'none';
                           }, 3000);
                       } else {
                           alert(data);
                       }
                   })
                 .catch(error => {
                       console.error('更新支付设置出错:', error);
                       alert('更新失败，请稍后重试');
                   });
           }
       </script>
<script src="../static/js/admin-shell.js"></script>
</body>

</html>


