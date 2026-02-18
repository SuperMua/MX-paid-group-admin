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
      body {
          margin: 0;
          background:
              radial-gradient(1200px 500px at 0% 0%, rgba(83, 86, 251, 0.15), transparent 55%),
              radial-gradient(900px 420px at 100% 0%, rgba(245, 57, 248, 0.12), transparent 50%),
              #f5f7ff;
      }

      .settings-container {
          width: min(1040px, calc(100% - 24px));
          margin: 68px auto 24px;
          border: 1px solid rgba(83, 86, 251, 0.16);
          border-radius: 18px;
          background: #fff;
          box-shadow: 0 18px 36px rgba(83, 86, 251, 0.12);
          padding: 20px;
      }

      .section-title {
          margin: 0 0 8px;
          color: #1f2a47;
          font-size: 22px;
          font-weight: 700;
      }

      .current-user {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          margin-bottom: 14px;
          padding: 8px 12px;
          border-radius: 999px;
          background: #eef0ff;
          color: #39426c;
          font-size: 14px;
          font-weight: 600;
      }

      .settings-intro {
          margin: 0 0 16px;
          font-size: 13px;
          line-height: 1.7;
          color: #667085;
      }

      .avatar-section {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 18px;
      }

      .avatar-container {
          width: 84px;
          height: 84px;
          border-radius: 16px;
          border: 1px solid rgba(83, 86, 251, 0.22);
          overflow: hidden;
          box-shadow: 0 8px 22px rgba(83, 86, 251, 0.14);
      }

      #avatar {
          width: 100%;
          height: 100%;
          object-fit: cover;
      }

      #upload-button {
          width: 84px;
          height: 84px;
          border-radius: 16px;
          border: 1px dashed rgba(83, 86, 251, 0.45);
          background: #f7f8ff;
          color: #7a80ca;
          font-size: 28px;
          cursor: pointer;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          transition: all 0.2s ease;
      }

      #upload-button:hover {
          border-color: #5356fb;
          color: #5356fb;
          box-shadow: 0 0 0 4px rgba(83, 86, 251, 0.14);
      }

      #avatar-input {
          display: none;
      }

      .input-group {
          margin-bottom: 14px;
      }

      .input-group label {
          display: block;
          margin-bottom: 7px;
          color: #4a5578;
          font-size: 14px;
          font-weight: 600;
      }

      .input-group input {
          width: 100%;
          padding: 11px 12px;
          border: 1px solid #d8d6ff;
          border-radius: 12px;
          color: #26324f;
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

      .settings-save-wrap {
          margin-top: 18px;
          display: flex;
          justify-content: flex-end;
      }

      .custom-popup {
          position: fixed;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: none;
          padding: 10px 18px;
          border-radius: 999px;
          background: rgba(38, 44, 75, 0.88);
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
        <div class="title">账号设置</div>
    </div>
    <div class="settings-container">
        <h2 class="section-title">账号安全与资料</h2>
        <div class="current-user">
            当前昵称：<span id="current-username"></span>
        </div>
        <p class="settings-intro">支持修改昵称、登录密码和头像，建议定期更新密码以提升后台安全性。</p>
        <div class="avatar-section">
            <div class="avatar-container">
                <img id="avatar" src="#" alt="用户头像">
            </div>
            <div id="upload-button">+</div>
            <input type="file" id="avatar-input" accept="image/*">
        </div>
        <div class="input-group">
            <label for="username">修改昵称</label>
            <input type="text" id="username" placeholder="请输入新的昵称（可留空）" autocomplete="username">
        </div>
        <div class="input-group">
            <label for="password">修改密码</label>
            <input type="password" id="password" placeholder="请输入新密码（留空则不修改）" autocomplete="new-password">
            <p class="input-tip">提示：密码留空时仅更新昵称或头像，不会覆盖原密码。</p>
        </div>
        <div class="settings-save-wrap">
            <button class="settings-save-btn" onclick="updateSettings()">保存账号设置</button>
        </div>
    </div>
    <div class="custom-popup" id="popup">修改成功</div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 获取当前用户信息
            fetch('upload_avatar.php?action=get_user_info')
              .then(response => response.json())
              .then(data => {
                    document.getElementById('current-username').textContent = data.username;
                    document.getElementById('avatar').src = data.avatar;
                })
              .catch(error => {
                    console.error('获取当前用户信息出错:', error);
                });
        });

        document.getElementById('upload-button').addEventListener('click', function () {
            document.getElementById('avatar-input').click();
        });

        document.getElementById('avatar-input').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    document.getElementById('avatar').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        function updateSettings() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const avatarFile = document.getElementById('avatar-input').files[0];

            const formData = new FormData();
            formData.append('action', 'update_settings');
            formData.append('username', username);
            formData.append('password', password);
            if (avatarFile) {
                formData.append('avatar', avatarFile);
            }

            fetch('upload_avatar.php', {
                method: 'POST',
                body: formData
            })
              .then(response => response.text())
              .then(data => {
                    if (data === '设置更新成功') {
                        const popup = document.getElementById('popup');
                        popup.style.display = 'block';
                        setTimeout(() => {
                            popup.style.display = 'none';
                        }, 3000);
                        // 更新成功后重新获取用户信息
                        fetch('upload_avatar.php?action=get_user_info')
                          .then(response => response.json())
                          .then(data => {
                                document.getElementById('current-username').textContent = data.username;
                                document.getElementById('avatar').src = data.avatar;
                            })
                          .catch(error => {
                                console.error('获取当前用户信息出错:', error);
                            });
                    } else {
                        alert(data);
                    }
                })
              .catch(error => {
                    console.error('Error:', error);
                    alert('更新失败，请稍后重试');
                });
        }
    </script>
<script src="../static/js/admin-shell.js"></script>
</body>

</html>



