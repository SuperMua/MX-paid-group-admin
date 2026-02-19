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
       .audit-switch-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 16px;
            border: 1px solid rgba(83, 86, 251, 0.2);
            border-radius: 18px;
            background: linear-gradient(135deg, #f7f8ff 0%, #f2f5ff 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

       .switch-text {
            font-size: 14px;
            color: #344054;
            font-weight: 600;
        }

       .switch-hint {
            margin-top: 6px;
            color: #98a2b3;
            font-size: 12px;
            line-height: 1.6;
        }

       .switch {
            position: relative;
            display: inline-block;
            width: 92px;
            height: 42px;
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
            background: linear-gradient(135deg, #c8cfef 0%, #e4e7f7 100%);
            transition: .28s ease;
            border-radius: 999px;
            border: 1px solid rgba(83, 86, 251, 0.2);
            box-shadow: inset 0 2px 4px rgba(18, 34, 78, 0.12);
        }

       .slider:before {
            position: absolute;
            content: "";
            height: 32px;
            width: 32px;
            left: 5px;
            bottom: 4px;
            background-color: white;
            transition: .28s ease;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.24);
        }

       .slider:after {
            content: '关闭';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #4f5675;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            transition: opacity 0.2s ease;
        }

        input:checked +.slider {
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
        }

        input:focus +.slider {
            box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.18);
        }

        input:checked +.slider:before {
            transform: translateX(49px);
        }

        input:checked +.slider:after {
            content: '开启';
            color: #ffffff;
            right: 14px;
        }

        #statusText {
            margin-top: 14px;
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
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

        .status-card {
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 18px;
            background: #fafaff;
            padding: 14px;
        }

        .status-card h4 {
            margin: 0 0 6px;
            font-size: 14px;
            color: #3e4b74;
        }

        .status-card p {
            margin: 0;
            font-size: 13px;
            color: #98a2b3;
            line-height: 1.6;
        }

       .audit-state-strip {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

       .audit-state-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(83, 86, 251, 0.2);
            background: #eef1ff;
            color: #4f5b88;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>

<body class="settings-pro-body">
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">审核设置</div>
    </div>
    <main class="settings-pro-shell">
        <header class="settings-pro-header">
            <h2 class="settings-pro-title">审核流程开关</h2>
            <p class="settings-pro-subtitle">开启后系统将自动审核提交内容，请谨慎评估业务风险并定期抽检结果。</p>
            <div class="audit-state-strip">
                <span class="audit-state-pill">高风险场景建议人工复核</span>
                <span class="audit-state-pill">开关切换后即时生效</span>
            </div>
        </header>

        <section class="settings-pro-card">
            <h3>自动审核状态</h3>
            <div class="audit-switch-row">
                <div>
                    <span class="switch-text">自动审核开关</span>
                    <p class="switch-hint">开启后系统将自动处理审核结果，建议每日抽检日志。</p>
                </div>
                <label class="switch">
                    <input type="checkbox" id="toggleSwitch" <?php echo $isAutoAuditEnabled? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div id="statusText"><?php echo $isAutoAuditEnabled? '自动审核已开启' : '自动审核已关闭'; ?></div>
        </section>

        <div class="settings-status-grid">
            <div class="status-card">
                <h4>推荐配置</h4>
                <p>高价值订单建议关闭自动审核，或配合人工复核名单。</p>
            </div>
            <div class="status-card">
                <h4>风险提醒</h4>
                <p>自动审核无法识别复杂图文语义，请每天抽样检查审核结果。</p>
            </div>
            <div class="status-card">
                <h4>运维建议</h4>
                <p>建议每天固定时段抽检审核日志，发现异常后切换为人工审核。</p>
            </div>
        </div>
    </main>
    <div class="settings-pro-toast" id="auditToast">状态已更新</div>
    <script>
        (function () {
        const pageRoot = document.querySelector('.settings-pro-shell');
        if (!pageRoot || pageRoot.dataset.boundAuditStatusPage === '1') {
            return;
        }
        pageRoot.dataset.boundAuditStatusPage = '1';

        const toggleSwitch = document.getElementById('toggleSwitch');
        const toggleShell = document.querySelector('.switch');
        const statusText = document.getElementById('statusText');
        const auditToast = document.getElementById('auditToast');
        let isSaving = false;

        function showToast(message, isError) {
            if (!auditToast) {
                return;
            }
            auditToast.textContent = message;
            auditToast.style.background = isError ? 'rgba(185,28,28,0.9)' : 'rgba(38,44,75,0.88)';
            auditToast.style.display = 'block';
            setTimeout(() => {
                auditToast.style.display = 'none';
            }, 2000);
        }

        function syncStatusClass(isEnabled) {
            statusText.classList.toggle('status-on', isEnabled);
            statusText.classList.toggle('status-off', !isEnabled);
        }

        function toggleAutoAudit() {
            if (isSaving) {
                return;
            }
            isSaving = true;
            if (toggleShell) {
                toggleShell.style.opacity = '0.65';
                toggleShell.style.pointerEvents = 'none';
            }
            const newStatus = toggleSwitch.checked? 1 : 0;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'audit_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) {
                    return;
                }

                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        statusText.textContent = newStatus? '自动审核已开启': '自动审核已关闭';
                        syncStatusClass(newStatus === 1);
                        showToast('自动审核状态已更新', false);
                    } else {
                        console.error('更新状态失败:', response.message);
                        // 恢复开关状态
                        toggleSwitch.checked =!toggleSwitch.checked;
                        syncStatusClass(toggleSwitch.checked);
                        showToast(response.message || '更新失败，请稍后重试', true);
                    }
                    if (toggleShell) {
                        toggleShell.style.opacity = '1';
                        toggleShell.style.pointerEvents = 'auto';
                    }
                    isSaving = false;
                    return;
                }

                toggleSwitch.checked = !toggleSwitch.checked;
                syncStatusClass(toggleSwitch.checked);
                showToast('请求失败，请稍后重试', true);
                if (toggleShell) {
                    toggleShell.style.opacity = '1';
                    toggleShell.style.pointerEvents = 'auto';
                }
                isSaving = false;
            };
            xhr.onerror = function () {
                toggleSwitch.checked = !toggleSwitch.checked;
                syncStatusClass(toggleSwitch.checked);
                showToast('网络异常，请稍后重试', true);
                if (toggleShell) {
                    toggleShell.style.opacity = '1';
                    toggleShell.style.pointerEvents = 'auto';
                }
                isSaving = false;
            };
            xhr.send('status=' + newStatus);
        }

        if (toggleSwitch) {
            syncStatusClass(toggleSwitch.checked);
            toggleSwitch.addEventListener('change', toggleAutoAudit);
        }
        })();
    </script>
<script src="../static/js/admin-shell.js"></script>
</body>

</html>

<?php
// 关闭数据库连接
$conn->close();
?>


