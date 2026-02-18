<?php
require_once 'login_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/admin.css">
    <title>账号设置</title>
    <style>
      #avatar-input {
          display: none;
      }

      .settings-account-grid {
          display: grid;
          gap: 14px;
      }

      .settings-account-brief {
          margin: 0;
          color: #667085;
          font-size: 13px;
          line-height: 1.7;
      }

      .settings-account-help {
          margin: 0;
          color: #98a2b3;
          font-size: 12px;
          line-height: 1.6;
      }
    </style>
</head>

<body class="settings-pro-body">
    <div class="navbar">
       <a class="back-button left-arrow" href="index.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">账号设置</div>
    </div>
    <main class="settings-pro-shell">
        <header class="settings-pro-header">
            <h2 class="settings-pro-title">账号安全与资料</h2>
            <p class="settings-pro-subtitle">支持修改昵称、密码与头像。建议每月轮换一次密码，减少后台账号风险。</p>
            <div class="settings-pro-tag">当前昵称：<span id="current-username"></span></div>
        </header>

        <div class="settings-account-grid">
            <section class="settings-pro-card">
                <h3>头像与基础资料</h3>
                <div class="settings-upload-row">
                    <div class="settings-upload-preview">
                        <img id="avatar" src="#" alt="用户头像">
                    </div>
                    <button id="upload-button" class="settings-upload-trigger" type="button">+</button>
                    <input type="file" id="avatar-input" accept="image/*">
                </div>
                <p class="settings-account-help">建议上传 1:1 比例头像，提升后台识别度。</p>
            </section>

            <section class="settings-pro-card">
                <h3>账号凭证</h3>
                <p class="settings-account-brief">昵称可选填，密码留空时不会覆盖现有密码。</p>
                <div class="settings-pro-grid two-col">
                    <div class="settings-pro-field">
                        <label for="username">修改昵称</label>
                        <input class="settings-pro-input" type="text" id="username" placeholder="请输入新的昵称（可留空）" autocomplete="username">
                    </div>
                    <div class="settings-pro-field">
                        <label for="password">修改密码</label>
                        <input class="settings-pro-input" type="password" id="password" placeholder="请输入新密码（留空则不修改）" autocomplete="new-password">
                        <p class="settings-pro-help">提示：密码留空时仅更新昵称或头像，不会覆盖原密码。</p>
                    </div>
                </div>
            </section>

            <section class="settings-pro-card">
                <h3>安全建议</h3>
                <ul class="settings-pro-inline-list">
                    <li>建议使用 8 位以上强密码，包含字母、数字和符号。</li>
                    <li>如多人共用后台，请定期修改账号密码并统一交接流程。</li>
                </ul>
            </section>
        </div>

        <div class="settings-pro-actions">
            <button class="settings-pro-primary" type="button">保存账号设置</button>
        </div>
    </main>

    <div class="settings-pro-toast" id="popup">修改成功</div>

    <script>
        (function () {
            const root = document.querySelector('.settings-pro-shell');
            if (!root || root.dataset.boundAccountSettings === '1') {
                return;
            }
            root.dataset.boundAccountSettings = '1';

            const uploadButton = document.getElementById('upload-button');
            const avatarInput = document.getElementById('avatar-input');
            const avatarImage = document.getElementById('avatar');
            const popup = document.getElementById('popup');
            const saveButton = document.querySelector('.settings-pro-primary');

            function syncCurrentUserInfo() {
                fetch('upload_avatar.php?action=get_user_info', { cache: 'no-store' })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('current-username').textContent = data.username || '';
                        avatarImage.src = data.avatar || '';
                    })
                    .catch(error => {
                        console.error('获取当前用户信息出错:', error);
                    });
            }

            function showToast(message, isError) {
                popup.textContent = message;
                popup.style.background = isError ? 'rgba(220,38,38,0.9)' : 'rgba(38,44,75,0.88)';
                popup.style.display = 'block';
                setTimeout(() => {
                    popup.style.display = 'none';
                }, 2200);
            }

            function updateSettings() {
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const avatarFile = avatarInput.files[0];

                const formData = new FormData();
                formData.append('action', 'update_settings');
                formData.append('username', username);
                formData.append('password', password);
                if (avatarFile) {
                    formData.append('avatar', avatarFile);
                }

                saveButton.disabled = true;
                saveButton.textContent = '保存中...';

                fetch('upload_avatar.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data === '设置更新成功') {
                            showToast('账号设置已更新', false);
                            syncCurrentUserInfo();
                        } else {
                            showToast(data || '更新失败', true);
                        }
                    })
                    .catch(error => {
                        console.error('更新账号设置失败:', error);
                        showToast('更新失败，请稍后重试', true);
                    })
                    .finally(() => {
                        saveButton.disabled = false;
                        saveButton.textContent = '保存账号设置';
                    });
            }

            uploadButton.addEventListener('click', function () {
                avatarInput.click();
            });

            avatarInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) {
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (event) {
                    avatarImage.src = event.target.result;
                };
                reader.readAsDataURL(file);
            });

            saveButton.addEventListener('click', updateSettings);
            syncCurrentUserInfo();
        })();
    </script>
<script src="../static/js/admin-shell.js"></script>
</body>

</html>



