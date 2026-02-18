<?php
require_once '../config/config.php';
require_once 'login_check.php';
// 初始化变量
$operationSuccess = false; // 用于标记操作是否成功
$updateSuccess = false; // 用于标记更新操作是否成功

// 获取当前设置
$amount = 0;
$text_field = '';
$sql = "SELECT amount, text_field FROM temp WHERE id = 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $amount = $row['amount'];
    $text_field = $row['text_field'];
}

// 检查是否有提交操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 处理金额和按钮文本更新
    if (isset($_POST['amount']) && isset($_POST['text_field'])) {
        $amount = $_POST['amount'];
        $text_field = $_POST['text_field'];

        $sql = "UPDATE temp SET amount = ?, text_field = ? WHERE id = 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ds", $amount, $text_field);
            if ($stmt->execute()) {
                $updateSuccess = true;
            } else {
                echo "更新失败：" . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "SQL 准备失败：" . $conn->error;
        }
    }

    // 处理群组操作
    if (isset($_POST['action'])) {
        $nickname = $_POST['nickname'] ?? '';
        $content = $_POST['content'] ?? '';
        $id = $_POST['id'] ?? 0;

        // 添加操作
        if ($_POST['action'] === 'add') {
            $group_avatar = '';
            
            // 处理头像上传
             if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                 // 验证文件类型
                 $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
                 // 使用 getimagesize 获取图片的 MIME 类型
                 $image_info = getimagesize($_FILES['avatar']['tmp_name']);
                 if ($image_info === false) {
                     die("上传的文件不是有效的图片");
                 }
    
                 $mime_type = $image_info['mime'];
                 if (!in_array($mime_type, $allowed_types)) {
                     die("只允许上传JPEG、PNG或GIF图片");
                 }
    
                // 限制文件大小 (例如2MB)
                if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                    die("图片大小不能超过2MB");
                }
    
                $avatar_tmp_name = $_FILES['avatar']['tmp_name'];
                $avatar_name = $_FILES['avatar']['name'];
                $avatar_extension = pathinfo($avatar_name, PATHINFO_EXTENSION);
                $avatar_new_name = uniqid() . '_qun.' . $avatar_extension;
                $avatar_path = '../static/images/' . $avatar_new_name;

                if (move_uploaded_file($avatar_tmp_name, $avatar_path)) {
                    $group_avatar = $avatar_path;
                } else {
                    echo "头像上传失败";
                    exit;
                }
            }
            
            // 数据库插入操作
            $sql = "INSERT INTO template (nickname, content, group_avatar) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sss", $nickname, $content, $group_avatar);
                if ($stmt->execute()) {
                    $operationSuccess = true;
                } else {
                    echo "插入失败：" . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "SQL 准备失败：" . $conn->error;
            }
        } 
        // 编辑操作
        elseif ($_POST['action'] === 'edit') {
            // 先获取当前头像路径
            $current_avatar = '';
            $sql = "SELECT group_avatar FROM template WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($current_avatar);
            $stmt->fetch();
            $stmt->close();
            
            // 处理新头像上传
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    // 验证文件类型
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    // 使用 getimagesize 获取图片的 MIME 类型
    $image_info = getimagesize($_FILES['avatar']['tmp_name']);
    if ($image_info === false) {
        die("上传的文件不是有效的图片");
    }

    $mime_type = $image_info['mime'];
    if (!in_array($mime_type, $allowed_types)) {
        die("只允许上传JPEG、PNG或GIF图片");
    }

    // 限制文件大小 (例如2MB)
    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        die("图片大小不能超过2MB");
    }

    $avatar_tmp_name = $_FILES['avatar']['tmp_name'];
    $avatar_name = $_FILES['avatar']['name'];
    $avatar_extension = pathinfo($avatar_name, PATHINFO_EXTENSION);
    $avatar_new_name = uniqid() . '_kefu.' . $avatar_extension;
    $avatar_path = '../static/images/' . $avatar_new_name;

    if (move_uploaded_file($avatar_tmp_name, $avatar_path)) {
        // 删除旧头像文件（如果存在且不是默认头像）
        if ($current_avatar && file_exists($current_avatar)) {
            @unlink($current_avatar);
        }
        $current_avatar = $avatar_path;
    } else {
        echo "头像上传失败";
        exit;
    }
}

            // 数据库更新操作
            $sql = "UPDATE template SET nickname = ?, content = ?, group_avatar = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssi", $nickname, $content, $current_avatar, $id);
                if ($stmt->execute()) {
                    $operationSuccess = true;
                } else {
                    echo "更新失败：" . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "SQL 准备失败：" . $conn->error;
            }
        } 
        // 删除操作
        elseif ($_POST['action'] === 'delete') {
            // 先获取头像路径以便删除文件
            $avatar_path = '';
            $sql = "SELECT group_avatar FROM template WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($avatar_path);
            $stmt->fetch();
            $stmt->close();
            
            // 删除数据库记录
            $sql = "DELETE FROM template WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    // 删除对应的头像文件
                    if ($avatar_path && file_exists($avatar_path)) {
                        @unlink($avatar_path);
                    }
                    $operationSuccess = true;
                } else {
                    echo "删除失败：" . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "SQL 准备失败：" . $conn->error;
            }
        }
    }
}

// 获取所有群组数据
$sql = "SELECT * FROM template ORDER BY id DESC";
$result = $conn->query($sql);
$groups = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }
}

$conn->close();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../static/css/admin.css">
    <title>模版二设置</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fafbfc;
            padding: 10px;
			margin-bottom: 40px;
			margin: 0;
        }

        /* 表单区域样式 */
       .form-section {
           /*max-width: 600px; /* 可根据需要调整 */
           margin: 0 auto; /* 整个表单区域居中 */
           padding: 20px 20px 10px 20px;
           background-color: white;
           border: 1px solid rgba(0, 0, 0, 0.08);
           border-radius: 8px;
           box-shadow: 0 10px 24px rgba(16, 24, 40, 0.05);
		   font-size: 14px;
		   margin-top: 40px;
       }
       
       .form-section form {
           display: flex;
           flex-direction: column;
           gap: 15px; /* 表单项之间的间距 */
       }
       
       .form-item {
           display: flex;
           align-items: center;
       }
       
       .form-item label {
           /*flex: 0 0 90px; /* 固定标签宽度 */
           margin-right: 10px;
          /* text-align: right; /* 标签文字右对齐 */
		  color: #8b8a8a;
       }
       
       .form-item input {
           flex: 1; /* 输入框占据剩余空间 */
           padding: 10px;
           border: 1px solid #ccc;
           border-radius: 5px;
       }
       
       .submit {
           padding: 10px 30px;
           background-color: #257ef8;
           color: white;
           border: none;
           border-radius: 4px;
           font-size: 14px;
           cursor: pointer;
           margin: 20px auto 0; /* 上下20px，左右auto实现水平居中 */
           display: block; /* 必须设置为block才能使margin auto生效 */
           width: fit-content; /* 按钮宽度根据内容自适应 */
       }

        /* 群聊列表区域样式 */
       .group-list-section {
            margin-top: 20px;
			margin-bottom: 40px;
        }

       .group-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

       .group-list-header h2 {
            margin: 0;
            font-size: 15px;
			margin-left: 10px;
			color: #8b8a8a;
        }

       .group-list-header button {
            color: #257ef8;
            text-decoration: none;
            font-size: 14px;
            margin-right: 10px;
			border: none; /* 完全移除边框 */
			background: transparent;
        }

       .group-item {
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(16, 24, 40, 0.05);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

       .group-avatar {
            width: 60px;
            height: 60px;
            background-color: #ddd;
            margin-right: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            border-radius: 4px;
        }
		.group-avatar img{
			width: 60px;
			height: 60px;
			border-radius: 5px;
		}

       .group-info {
            flex: 1;
        }

       .group-name {
            font-size: 15px;
            margin-bottom: 5px;
        }

       .group-message {
            font-size: 13px;
            color: #8b8a8a;
        }

       .group-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

       .group-actions button {
            padding: 5px 10px;
            margin-bottom: 5px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

       .edit-button {
            background-color: #257ef8;
            color: white;
        }

       .delete-button {
            background-color: #e74c3c;
            color: white;
        }

        /* 弹窗通用样式 */
       .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

       .popup-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            position: relative; /* 新增相对定位，作为关闭按钮的参考容器 */
       }
        .popup-content2 {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 220px;
            position: relative; /* 新增相对定位，作为关闭按钮的参考容器 */
       }

       .popup-header {
            text-align: center;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .popup-close {
            position: absolute; /* 绝对定位，相对于popup-content定位 */
            top: 0px; /* 距离顶部10px */
            right: 10px; /* 距离右侧10px */
            cursor: pointer;
            font-size: 30px; /* 适当增大字体大小，视觉更明显 */
        }

       .form-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
			color: #8b8a8a;
			font-size: 14px;
        }

       .form-group label {
            margin-right: 10px;
        }

       .form-group input {
            flex: 2;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
       
	   .avatar-upload {
	       width: 80px;
	       height: 80px;
	       border: 1px dashed #ccc;
	       border-radius: 4px;
	       display: flex;
	       justify-content: center;
	       align-items: center;
	       margin: 0 auto 40px;
	       cursor: pointer;
	       position: relative;
	       overflow: hidden;
	   }
	   
	   .avatar-upload .avatar-label {
	       display: flex;
	       justify-content: center;
	       align-items: center;
	       width: 100%;
	       height: 100%;
	       position: absolute;
	       z-index: 1;
	       background-color: rgba(255, 255, 255, 0.8); /* 背景颜色，可选 */
		   font-size: 14px;
	   }
	   
	   .avatar-upload .avatar-label small {
	       font-size: 24px;
	       color: #ccc;
	   }
	   
	   .avatar-upload img {
	       width: 100%;
	       height: 100%;
	       border-radius: 4px;
	       display: none; /* 默认隐藏 */
	       object-fit: cover; /* 确保图片覆盖整个容器 */
	       position: absolute;
	       top: 0;
	       left: 0;
	       z-index: 2; /* 确保图片在加号上方 */
	   }
	   .avatar-upload input[type="file"] {
	       position: absolute;
	       top: 0;
	       left: 0;
	       width: 100%;
	       height: 100%;
	       opacity: 0;
	       cursor: pointer;
	       z-index: 3; /* 修改为正值确保点击优先级 */
	   }
	   
       .button-group {
            text-align: center;
            margin-top: 20px;
        }

       .button-group button {
            padding: 10px 30px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

       .confirm-btn {
            background-color: #257ef8;
            color: white;
        }

       .cancel-btn {
            background-color: #ddd;
        }
		
		/* 成功提示弹窗样式 */
		.success-popup {
		    display: none; /* 默认隐藏 */
		    position: fixed;
		    top: 50%;
		    left: 50%;
		    transform: translate(-50%, -50%);
		    width: 100px;
		    height: 40px;
		    background-color: rgba(0, 0, 0, 0.7);
		    color: white;
		    text-align: center;
		    line-height: 40px;
		    border-radius: 5px;
		    font-size: 14px;
		    z-index: 1000; /* 确保在最上层 */
		    animation: fadeInOut 2s ease-in-out; /* 可选：添加淡入淡出效果 */
		}

		@media (min-width: 992px) {
			body {
				padding: 0;
			}

			.form-section,
			.group-list-section {
				width: min(1180px, calc(100% - 64px));
				margin-left: auto;
				margin-right: auto;
				box-sizing: border-box;
			}

			.form-section {
				margin-top: 76px;
				padding: 24px;
			}

			.group-list-section {
				margin-top: 16px;
				display: grid;
				gap: 12px;
				margin-bottom: 24px;
			}

			.group-list-header {
				padding: 0 8px;
			}

			.group-item {
				margin-bottom: 0;
				display: grid;
				grid-template-columns: 72px 1fr auto;
				gap: 14px;
				align-items: center;
				padding: 14px;
			}

			.group-avatar,
			.group-avatar img {
				width: 72px;
				height: 72px;
			}

			.popup-content {
				width: min(560px, calc(100% - 32px));
			}

			.popup-content2 {
				width: min(320px, calc(100% - 32px));
			}

			.popup-content .form-group {
				display: grid;
				grid-template-columns: 70px 1fr;
				gap: 12px;
				align-items: center;
			}

			.popup-content .button-group {
				text-align: right;
			}
		}	
    </style>
</head>

<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="moban.php"></a>
        <div class="title">模版二设置</div>
    </div>
    <!-- 表单区域 -->
    <div class="form-section">
        <form method="post">
            <div class="form-item">
                <label for="amount">入群金额：</label>
                <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" step="0.01">
            </div>
            <div class="form-item">
                <label for="buttonText">底部按钮：</label>
                <input type="text" id="buttonText" name="text_field" value="<?php echo htmlspecialchars($text_field); ?>"> 
            </div>
            <button type="submit" class="submit">确认</button>
        </form>
    </div>

    <!-- 群聊列表区域 -->
    <div class="group-list-section">
        <div class="group-list-header">
            <h2>群聊列表</h2>
            <button onclick="handleAddClick()">添加</button>
        </div>
        <?php foreach ($groups as $group): ?>
            <div class="group-item" data-id="<?php echo $group['id']; ?>">
                <div class="group-avatar"><img src="<?php echo $group['group_avatar']; ?>" alt="群头像"></div>
                <div class="group-info">
                    <div class="group-name"><?php echo htmlspecialchars($group['nickname']); ?></div>
                    <div class="group-message"><?php echo htmlspecialchars($group['content']); ?></div>
                </div>
                <div class="group-actions">
                    <button class="edit-button" onclick="handleEditClick(<?php echo $group['id']; ?>)">编辑</button>
                    <button class="delete-button" onclick="handleDeleteClick(<?php echo $group['id']; ?>)">删除</button>
                </div>
            </div>
		<?php endforeach; ?>
    </div>

    <!-- 通用弹窗（添加/编辑） -->
    <div class="popup" id="common-popup">
        <div class="popup-content">
            <span class="popup-close" onclick="hideCommonPopup()">&times;</span>
            <div class="popup-header">添加群组</div>
           <div class="avatar-upload">
               <input type="file" name="avatar" id="avatar-upload" onchange="previewAvatar()" accept="image/*">
               <label for="avatar-upload" class="avatar-label">
                   <small>+</small>
                   <img id="avatar-preview" src="" alt="头像预览">
               </label>
           </div>
		   
            <div class="form-group">
                <label>群昵称：</label>
                <input type="text" name="nickname" placeholder="请填写群昵称">
            </div>
            <div class="form-group">
                <label>群消息：</label>
                <input type="text" name="content" placeholder="请填写群消息">
            </div>
            <div class="form-group">
                <input type="hidden" name="id">
            </div>
            <div class="button-group">
                <button class="confirm-btn" onclick="handleConfirm()">确认</button>
            </div>
        </div>
    </div>

    <!-- 删除确认弹窗 -->
    <div class="popup" id="delete-confirm-popup">
        <div class="popup-content2">
            <div class="popup-header">确认删除吗？</div>
            <div class="button-group">
                <button class="cancel-btn" onclick="hideDeleteConfirmPopup()">取消</button>
                <button class="confirm-btn" onclick="handleDeleteConfirm()">确认</button>
            </div>
        </div>
    </div>
	
	<!-- 弹窗容器 -->
	<div id="successPopup" class="success-popup">操作成功</div>

	<script>
	  // 头像预览功能
	  function previewAvatar() {
	      const avatarInput = document.getElementById('avatar-upload');
	      const avatarPreview = document.getElementById('avatar-preview');
	      const plusSign = document.querySelector('.avatar-label small');
	      
	      if (avatarInput.files && avatarInput.files[0]) {
	          const reader = new FileReader();
	          reader.onload = function(e) {
	              avatarPreview.src = e.target.result;
	              avatarPreview.style.display = 'block';
	              plusSign.style.display = 'none';
	          };
	          reader.readAsDataURL(avatarInput.files[0]);
	      } else {
	          avatarPreview.style.display = 'none';
	          plusSign.style.display = 'block';
	      }
	  }
	
	
	  // 显示通用弹窗（添加/编辑）
	    function showCommonPopup(title, id = null) {
	        const popup = document.getElementById('common-popup');
	        popup.querySelector('.popup-header').textContent = title;
	        if (id) {
	            popup.querySelector('input[name="id"]').value = id;
	        }
	        popup.style.display = 'flex';
	    }
	
	    // 隐藏通用弹窗
	    function hideCommonPopup() {
	        document.getElementById('common-popup').style.display = 'none';
	    }
	
	    // 显示删除确认弹窗
	    function showDeleteConfirmPopup(id) {
	        document.getElementById('delete-confirm-popup').style.display = 'flex';
	        document.getElementById('delete-confirm-popup').setAttribute('data-id', id);
	    }
	
	    // 隐藏删除确认弹窗
	    function hideDeleteConfirmPopup() {
	        document.getElementById('delete-confirm-popup').style.display = 'none';
	    }
	
	    // 模拟编辑按钮点击事件处理
	    function handleEditClick(id) {
	        const popup = document.getElementById('common-popup');
	        const nicknameInput = popup.querySelector('input[name="nickname"]');
	        const contentInput = popup.querySelector('input[name="content"]');
	        const avatarPreview = popup.querySelector('#avatar-preview');
	
	        // 获取当前群组信息
	        const group = document.querySelector(`.group-item[data-id="${id}"]`);
	        const nickname = group.querySelector('.group-name').textContent;
	        const content = group.querySelector('.group-message').textContent;
	        const avatar = group.querySelector('.group-avatar img').src;
	
	        // 填充表单
	        nicknameInput.value = nickname;
	        contentInput.value = content;
	        avatarPreview.src = avatar;
	        avatarPreview.style.display = 'block';
	
	        showCommonPopup('编辑群组', id);
	    }
	
	    // 模拟删除按钮点击事件处理
	    function handleDeleteClick(id) {
	        showDeleteConfirmPopup(id);
	    }
	
	    // 模拟添加按钮点击事件处理
	    function handleAddClick() {
	        showCommonPopup('添加群组');
	    }
	
	    // 模拟确认操作
	    function handleConfirm() {
	        const popup = document.getElementById('common-popup');
	        const nickname = popup.querySelector('input[name="nickname"]').value;
	        const content = popup.querySelector('input[name="content"]').value;
	        const avatar = popup.querySelector('input[name="avatar"]').files[0];
	        const id = popup.querySelector('input[name="id"]').value;
	
	        const formData = new FormData();
	        formData.append('nickname', nickname);
	        formData.append('content', content);
	        if (avatar) {
	            formData.append('avatar', avatar);
	        }
	        formData.append('action', id ? 'edit' : 'add');
	        if (id) {
	            formData.append('id', id);
	        }
	
	        fetch('settings_page2.php', {
	            method: 'POST',
	            body: formData
	        }).then(response => response.text())
	          .then(data => {
	              console.log(data);
	              if (data.includes('操作成功')) {
	                  showSuccessPopup();
	                  hideCommonPopup();
	                  location.reload(); // 刷新页面以更新群聊列表
	              } else {
	                  alert('操作失败');
	              }
	          }).catch(error => {
	              console.error('Error:', error);
	          });
	    }
	
	    // 模拟删除确认操作
	    function handleDeleteConfirm() {
	        const popup = document.getElementById('delete-confirm-popup');
	        const id = popup.getAttribute('data-id');
	
	        const formData = new FormData();
	        formData.append('action', 'delete');
	        formData.append('id', id);
	
	        fetch('settings_page2.php', {
	            method: 'POST',
	            body: formData
	        }).then(response => response.text())
	          .then(data => {
	              console.log(data);
	              if (data.includes('操作成功')) {
	                  showSuccessPopup();
	                  hideDeleteConfirmPopup();
	                  location.reload(); // 刷新页面以更新群聊列表
	              } else {
	                  alert('删除失败');
	              }
	          }).catch(error => {
	              console.error('Error:', error);
	          });
	    }
	
	    // 弹窗提示
	    function showSuccessPopup() {
	        const popup = document.getElementById('successPopup');
	        if (popup) {
	            popup.style.display = 'block';
	            setTimeout(() => {
	                popup.style.display = 'none';
	            }, 2000);
	        } else {
	            console.error('Success popup element not found');
	        }
	    }
	
	    // 检查是否有操作成功的标志
	    <?php if ((isset($operationSuccess) && $operationSuccess) || (isset($updateSuccess) && $updateSuccess)): ?>
	        // 等待DOM加载完成
	        if (document.readyState === 'loading') {
	            document.addEventListener('DOMContentLoaded', showSuccessPopup);
	        } else {
	            showSuccessPopup();
	        }
	    <?php endif; ?>
	</script>
</body>
</html>
     
