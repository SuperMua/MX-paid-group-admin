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
       .payment-remark {
           margin: 0;
           color: #667085;
           font-size: 13px;
           line-height: 1.7;
       }

       .payment-badge {
           margin-top: 14px;
           padding: 8px 12px;
           border-radius: 999px;
           font-size: 12px;
           color: #5f6a95;
           background: #f1f3ff;
           display: inline-flex;
       }

       .payment-link-tip {
           margin-top: 8px;
           color: #98a2b3;
           font-size: 12px;
       }
    </style>
</head>

<body class="settings-pro-body">
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">支付设置</div>
    </div>
    <main class="settings-pro-shell">
       <header class="settings-pro-header">
           <h2 class="settings-pro-title">支付网关参数</h2>
           <p class="settings-pro-subtitle">请填写支付平台参数，保存后立即生效。建议修改后先通过一笔测试订单验证回调链路。</p>
       </header>

       <div class="settings-pro-grid two-col">
           <section class="settings-pro-card">
               <h3>支付通道</h3>
               <div class="settings-pro-field">
                   <label for="api_url">支付地址 API</label>
                   <input class="settings-pro-input" type="url" id="api_url" placeholder="例如：https://pay.example.com/" spellcheck="false">
                   <p class="settings-pro-help">示例：https://你的支付域名/ ，末尾建议保留 / 。</p>
               </div>
               <div class="settings-pro-field">
                   <label for="merchant_id">商户 ID</label>
                   <input class="settings-pro-input" type="text" id="merchant_id" placeholder="请输入商户 ID（数字或字母）" spellcheck="false">
               </div>
               <div class="settings-pro-field">
                   <label for="secre_key">商户秘钥 Key</label>
                   <input class="settings-pro-input" type="text" id="secre_key" placeholder="请输入商户密钥 Key" spellcheck="false">
               </div>
           </section>

           <section class="settings-pro-card">
               <h3>回调与安全</h3>
               <div class="settings-pro-field">
                   <label for="callback_url">回调地址</label>
                   <input class="settings-pro-input" type="url" id="callback_url" placeholder="例如：https://example.com（自动拼接 /pay/notify_url.php）" spellcheck="false">
                   <p class="settings-pro-help">填写站点基础域名即可，系统会自动拼接回调路径；若填写完整 notify/return 地址也会自动识别。</p>
               </div>
               <p class="payment-remark">修改参数后，建议立刻发起一笔小额支付进行验证，确认“下单 -> 支付 -> 回调 -> 状态更新”全链路正常。</p>
           </section>
       </div>

        <div class="settings-pro-actions">
            <button class="settings-pro-primary" type="button" id="savePaymentBtn">保存支付设置</button>
            <button class="settings-pro-secondary" type="button" id="openGatewayBtn">打开易支付网关</button>
        </div>
		<p class="payment-badge" id="paymentBadge">本系统已对接易支付</p>
        <p class="payment-link-tip">“打开易支付网关”用于快速校验第三方地址可达性，不会修改任何配置。</p>
    </main>
    <div class="settings-pro-toast" id="popup">更新成功</div>

   <script>
       (function () {
           const apiUrlInput = document.getElementById('api_url');
           const merchantInput = document.getElementById('merchant_id');
           const keyInput = document.getElementById('secre_key');
           const callbackInput = document.getElementById('callback_url');
           const toast = document.getElementById('popup');
           const badge = document.getElementById('paymentBadge');
           const saveButton = document.getElementById('savePaymentBtn');
           const openGatewayButton = document.getElementById('openGatewayBtn');

           if (!apiUrlInput || !saveButton || !openGatewayButton) {
               return;
           }

           function showToast(message, isError) {
               toast.textContent = message;
               toast.style.background = isError ? 'rgba(220,38,38,0.9)' : 'rgba(38,44,75,0.88)';
               toast.style.display = 'block';
               setTimeout(function () {
                   toast.style.display = 'none';
               }, 2200);
           }

           function normalizeHttpUrl(value, forceTrailingSlash) {
               let finalValue = (value || '').trim();
               if (!finalValue) {
                   return '';
               }

               if (!/^https?:\/\//i.test(finalValue)) {
                   finalValue = 'https://' + finalValue;
               }

               try {
                   const url = new URL(finalValue);
                   if (url.protocol !== 'http:' && url.protocol !== 'https:') {
                       return null;
                   }

                   if (forceTrailingSlash) {
                       url.pathname = url.pathname.replace(/\/(submit|mapi|api)\.php$/i, '/');
                       url.pathname = url.pathname.endsWith('/') ? url.pathname : url.pathname + '/';
                   } else {
                       url.pathname = url.pathname.replace(/\/+$/, '');
                   }
                   url.search = '';
                   url.hash = '';
                   return url.toString();
               } catch (error) {
                   return null;
               }
           }

           function normalizeCallbackBase(value) {
               const normalized = normalizeHttpUrl(value, false);
               if (normalized === null || normalized === '') {
                   return normalized;
               }
               return normalized.replace(/\/pay\/(notify_url|return_url)\.php$/i, '').replace(/\/+$/, '');
           }

           function syncBadge() {
               if (!badge) {
                   return;
               }
               const apiUrl = normalizeHttpUrl(apiUrlInput.value, true);
               if (!apiUrl) {
                   badge.textContent = '本系统已对接易支付（请先完善网关地址）';
                   return;
               }
               badge.textContent = '当前网关：' + apiUrl;
           }

           function fillForm(data) {
               apiUrlInput.value = data.api_url || '';
               merchantInput.value = data.merchant_id || '';
               keyInput.value = data.secre_key || '';
               callbackInput.value = data.callback_url || '';
               syncBadge();
           }

           function loadPaymentSettings() {
               fetch('payment_get.php', { cache: 'no-store' })
                   .then(function (response) { return response.json(); })
                   .then(function (data) { fillForm(data || {}); })
                   .catch(function (error) {
                       console.error('获取支付设置信息出错:', error);
                       showToast('读取支付设置失败', true);
                   });
           }

           function updatePaymentSettings() {
               const apiUrl = normalizeHttpUrl(apiUrlInput.value, true);
               if (apiUrl === null) {
                   showToast('支付地址格式错误', true);
                   return;
               }

               const callbackValue = callbackInput.value.trim() || window.location.origin;
               const callbackUrl = normalizeCallbackBase(callbackValue);
               if (callbackUrl === null) {
                   showToast('回调地址格式错误', true);
                   return;
               }

               const formData = new FormData();
               formData.append('api_url', apiUrl || '');
               formData.append('merchant_id', merchantInput.value.trim());
               formData.append('secre_key', keyInput.value.trim());
               formData.append('callback_url', callbackUrl || '');

               saveButton.disabled = true;
               saveButton.textContent = '保存中...';

               fetch('payment_get.php', {
                   method: 'POST',
                   body: formData
               })
                   .then(function (response) { return response.text(); })
                   .then(function (data) {
                       if (data === '更新成功') {
                           apiUrlInput.value = apiUrl || '';
                           callbackInput.value = callbackUrl || '';
                           syncBadge();
                           showToast('支付配置已更新');
                       } else {
                           showToast(data || '保存失败', true);
                       }
                   })
                   .catch(function (error) {
                       console.error('更新支付设置出错:', error);
                       showToast('更新失败，请稍后重试', true);
                   })
                   .finally(function () {
                       saveButton.disabled = false;
                       saveButton.textContent = '保存支付设置';
                   });
           }

           function openGatewayLink() {
               const apiUrl = normalizeHttpUrl(apiUrlInput.value, true);
               if (!apiUrl) {
                   showToast('请先填写并保存支付地址', true);
                   return;
               }
               const target = apiUrl + 'submit.php';
               const openedWindow = window.open(target, '_blank', 'noopener');
               if (!openedWindow) {
                   showToast('浏览器拦截了新窗口，请允许弹窗后重试', true);
                   return;
               }
               showToast('已打开网关测试页');
           }

           saveButton.addEventListener('click', updatePaymentSettings);
           openGatewayButton.addEventListener('click', openGatewayLink);
           apiUrlInput.addEventListener('input', syncBadge);
           loadPaymentSettings();
       })();
       </script>
<script src="../static/js/admin-shell.js"></script>
</body>

</html>


