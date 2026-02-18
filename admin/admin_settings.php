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
      
      .current-user {
          margin-bottom: 20px;
          font-size: 16px;
          color: #333;
      }
      
      .avatar-section {
          display: flex;
          align-items: center;
          margin-bottom: 20px;
      }
      
      .avatar-label {
          margin-bottom: 5px;
      }
      
      .avatar-container {
          position: relative;
          width: 80px;
          height: 80px;
          margin-right: 10px;
		  border: 1px solid #dbdbdb;
		  border-radius: 5px;
		  
      }
      
      #avatar {
          width: 100%;
          height: 100%;
          object-fit: cover;
          border-radius: 5px;
      }
      
      #upload-button {
          width: 80px;
          height: 80px;
          border: 1px dashed #ccc;
          border-radius: 5px;
          background-color: rgba(255, 255, 255, 0.7);
          display: flex;
          justify-content: center;
          align-items: center;
          font-size: 30px;
          color: #ccc;
          cursor: pointer;
      }
      
      #avatar-input {
          display: none;
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
			  width: min(620px, 100%);
		  }

		  button {
			  width: 220px;
			  align-self: flex-end;
			  margin-top: 12px;
			  border-radius: 10px;
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
        <div class="current-user">
            当前昵称：<span id="current-username"></span>
        </div>
        <div class="avatar-section">
            <div class="avatar-container">
                <img id="avatar" src="#" alt="用户头像">
            </div>
            <div id="upload-button">+</div>
            <input type="file" id="avatar-input" accept="image/*">
        </div>
        <div class="input-group">
            <label for="username">修改昵称</label>
            <input type="text" id="username" placeholder="请输入用户名">
        </div>
        <div class="input-group">
            <label for="password">修改密码</label>
            <input type="password" id="password" placeholder="请输入密码">
        </div>
        <button onclick="updateSettings()">确认修改</button>
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
</body>

</html>
