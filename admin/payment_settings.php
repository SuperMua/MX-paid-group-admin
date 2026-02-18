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
           font-family: Arial, sans-serif;
           display: flex;
           justify-content: center;
           align-items: flex-start;
           background-color: #fafbfc;
           margin: 0;
       }
       
       .settings-container {
           background-color: #fff;
           border: 1px solid rgba(0, 0, 0, 0.08);
           padding: 20px;
           border-radius: 12px;
           box-shadow: 0 10px 24px rgba(16, 24, 40, 0.05);
           width: 90%;
           display: flex;
           flex-direction: column;
           align-items: flex-start;
           margin: 50px 10px 0px 10px;
       }
       
       .input-group {
           margin-bottom: 15px;
           width: 100%;
           display: flex;
           flex-direction: column;
           align-items: center; /* 让整个输入框组居中 */
       }
       
       .input-group label {
           display: block;
           margin-bottom: 5px;
           font-size: 14px;
           color: #666;
           width: 90%; /* 与输入框宽度一致，方便对齐 */
           text-align: left; /* 标签文字靠左 */
       }
       
       .input-group input {
           width: 90%; /* 可根据需要调整输入框宽度 */
           padding: 10px;
           border: 1px solid #ccc;
           border-radius: 5px;
       }
       
       button {
           width: 100%;
           padding: 10px;
           background-color: #007BFF;
           color: #fff;
           border: none;
           border-radius: 20px;
           cursor: pointer;
       	   margin-top: 30px;
		   margin-bottom: 20px;
       }
       
       button:hover {
           background-color: #0056b3;
       }
       
       .custom-popup {
           position: fixed;
           top: 30%;
           left: 50%;
           transform: translate(-50%, -50%);
           background-color: rgba(0, 0, 0, 0.5);
           color: white;
           padding: 10px 20px;
           border-radius: 5px;
           z-index: 1000;
           display: none;
       	   font-size: 14px;
       }
	   .input-xt{
		   margin: auto;
		   font-size: 14px;
		   color: #ccc;
	   }

	   @media (min-width: 992px) {
		   .settings-container {
			   width: min(980px, calc(100% - 64px));
			   margin: 76px auto 24px;
			   padding: 24px 28px;
		   }

		   .input-group {
			   align-items: flex-start;
		   }

		   .input-group label,
		   .input-group input {
			   width: min(760px, 100%);
		   }

		   button {
			   width: 220px;
			   align-self: flex-end;
			   margin-bottom: 8px;
			   border-radius: 10px;
		   }
	   }
    </style>
</head>

<body>
<div class="navbar">
        <a class="back-button left-arrow" href="#" onclick="history.length>1 ? history.back() : location.href=document.referrer||'/';"></a>
        <div class="title">支付设置</div>
    </div>
    <div class="settings-container">
	   <p>完善支付接口</p>
        <div class="input-group">
            <label for="api_url">支付地址api</label>
            <input type="text" id="api_url" placeholder="请输入支付接口地址 格式https://域名/">
        </div>
        <div class="input-group">
            <label for="merchant_id">商户id</label>
            <input type="text" id="merchant_id" placeholder="请输入商户 ID">
        </div>
        <div class="input-group">
            <label for="secre_key">商户秘钥key</label>
            <input type="text" id="secre_key" placeholder="请输入商户密钥">
        </div>
		<div class="input-group">
		    <label for="callback_url">回调地址</label>
		    <input type="text" id="callback_url" placeholder="请输入回调地址 格式http://域名">
		</div>
        <button onclick="updatePaymentSettings()">确认更新</button>
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
</body>

</html>
