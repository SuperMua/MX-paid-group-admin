<?php
require_once '../config/config.php';
require_once 'login_check.php';

$isAjaxRequest = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

function outputActionJson($success, $message) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        array('success' => (bool)$success, 'message' => (string)$message),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

// 初始化变量
$operationSuccess = false; // 用于标记操作是否成功
$updateSuccess = false; // 用于标记更新操作是否成功
$actionResult = array('success' => false, 'message' => '操作失败，请稍后重试');

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
                     if ($isAjaxRequest) {
                         outputActionJson(false, "上传的文件不是有效的图片");
                     }
                     die("上传的文件不是有效的图片");
                 }
    
                 $mime_type = $image_info['mime'];
                 if (!in_array($mime_type, $allowed_types)) {
                     if ($isAjaxRequest) {
                         outputActionJson(false, "只允许上传JPEG、PNG或GIF图片");
                     }
                     die("只允许上传JPEG、PNG或GIF图片");
                 }
    
                // 限制文件大小 (例如2MB)
                if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                    if ($isAjaxRequest) {
                        outputActionJson(false, "图片大小不能超过2MB");
                    }
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
                    if ($isAjaxRequest) {
                        outputActionJson(false, "头像上传失败");
                    }
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
                    $actionResult = array('success' => true, 'message' => '添加成功');
                } else {
                    $actionResult = array('success' => false, 'message' => '插入失败：' . $stmt->error);
                    echo "插入失败：" . $stmt->error;
                }
                $stmt->close();
            } else {
                $actionResult = array('success' => false, 'message' => 'SQL 准备失败：' . $conn->error);
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
        if ($isAjaxRequest) {
            outputActionJson(false, "上传的文件不是有效的图片");
        }
        die("上传的文件不是有效的图片");
    }

    $mime_type = $image_info['mime'];
    if (!in_array($mime_type, $allowed_types)) {
        if ($isAjaxRequest) {
            outputActionJson(false, "只允许上传JPEG、PNG或GIF图片");
        }
        die("只允许上传JPEG、PNG或GIF图片");
    }

    // 限制文件大小 (例如2MB)
    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        if ($isAjaxRequest) {
            outputActionJson(false, "图片大小不能超过2MB");
        }
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
        if ($isAjaxRequest) {
            outputActionJson(false, "头像上传失败");
        }
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
                    $actionResult = array('success' => true, 'message' => '编辑成功');
                } else {
                    $actionResult = array('success' => false, 'message' => '更新失败：' . $stmt->error);
                    echo "更新失败：" . $stmt->error;
                }
                $stmt->close();
            } else {
                $actionResult = array('success' => false, 'message' => 'SQL 准备失败：' . $conn->error);
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
                    $actionResult = array('success' => true, 'message' => '删除成功');
                } else {
                    $actionResult = array('success' => false, 'message' => '删除失败：' . $stmt->error);
                    echo "删除失败：" . $stmt->error;
                }
                $stmt->close();
            } else {
                $actionResult = array('success' => false, 'message' => 'SQL 准备失败：' . $conn->error);
                echo "SQL 准备失败：" . $conn->error;
            }
        }
    }
}

if ($isAjaxRequest && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    outputActionJson($actionResult['success'], $actionResult['message']);
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
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../static/css/admin.css">
    <title>模版二设置</title>
    <style>
        body {
            margin: 0;
            background:
                radial-gradient(1200px 460px at 0% 0%, rgba(83, 86, 251, 0.14), transparent 55%),
                radial-gradient(900px 420px at 100% 0%, rgba(245, 57, 248, 0.1), transparent 50%),
                #f5f7ff;
            padding-bottom: 18px;
        }

        .form-section,
        .group-list-section {
            width: min(1180px, calc(100% - 24px));
            margin-left: auto;
            margin-right: auto;
            box-sizing: border-box;
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 18px 34px rgba(83, 86, 251, 0.12);
        }

        .form-section {
            margin-top: 68px;
            padding: 20px;
        }

        .panel-head {
            margin-bottom: 14px;
        }

        .panel-head h2 {
            margin: 0 0 8px;
            font-size: 22px;
            color: #1f2a47;
        }

        .panel-head p {
            margin: 0;
            font-size: 13px;
            color: #667085;
            line-height: 1.7;
        }

        .form-section form {
            display: grid;
            gap: 12px;
        }

        .form-item {
            display: grid;
            grid-template-columns: 88px minmax(0, 1fr);
            align-items: center;
            gap: 10px;
        }

        .form-item label {
            color: #4a567a;
            font-size: 14px;
            font-weight: 600;
        }

        .form-item input {
            padding: 11px 12px;
            border: 1px solid #d8d6ff;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-item input:focus,
        .form-group input:focus {
            border-color: #5356fb;
            box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.14);
        }

        .submit {
            justify-self: end;
            min-width: 160px;
            border: none;
            border-radius: 999px;
            padding: 11px 20px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            box-shadow: 0 14px 26px rgba(83, 86, 251, 0.26);
        }

        .group-list-section {
            margin-top: 14px;
            margin-bottom: 22px;
            padding: 16px;
        }

        .group-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .group-list-header h2 {
            margin: 0;
            font-size: 16px;
            color: #2f3b64;
        }

        .group-list-header button {
            border: none;
            border-radius: 999px;
            padding: 9px 16px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            cursor: pointer;
            box-shadow: 0 12px 22px rgba(83, 86, 251, 0.22);
        }

        .group-item {
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 14px;
            background: #fafaff;
            padding: 12px;
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            margin-bottom: 10px;
        }

        .group-item:last-child {
            margin-bottom: 0;
        }

        .group-avatar {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            border: 1px solid rgba(83, 86, 251, 0.2);
            overflow: hidden;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .group-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .group-name {
            font-size: 15px;
            font-weight: 700;
            color: #344054;
            margin-bottom: 4px;
        }

        .group-message {
            font-size: 13px;
            color: #667085;
            line-height: 1.6;
            word-break: break-all;
        }

        .group-actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .group-actions button {
            border: none;
            border-radius: 999px;
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            min-width: 72px;
        }

        .edit-button {
            color: #fff;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
        }

        .delete-button {
            color: #fff;
            background: linear-gradient(135deg, #ff6464 0%, #f53939 100%);
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(10, 13, 32, 0.64);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 12px;
            box-sizing: border-box;
        }

        .popup-content,
        .popup-content2 {
            width: min(560px, calc(100% - 10px));
            background: #fff;
            border: 1px solid rgba(83, 86, 251, 0.18);
            border-radius: 16px;
            padding: 18px;
            position: relative;
            box-shadow: 0 24px 44px rgba(8, 16, 35, 0.36);
        }

        .popup-content2 {
            width: min(340px, calc(100% - 10px));
        }

        .popup-header {
            text-align: left;
            margin-bottom: 14px;
            font-size: 16px;
            font-weight: 700;
            color: #2e3a63;
        }

        .popup-close {
            position: absolute;
            top: 4px;
            right: 12px;
            cursor: pointer;
            font-size: 30px;
            color: #6c76a1;
            line-height: 1;
        }

        .form-group {
            margin-bottom: 12px;
            display: grid;
            grid-template-columns: 72px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            color: #4a567a;
            font-size: 14px;
        }

        .form-group input {
            padding: 9px 10px;
            border: 1px solid #d8d6ff;
            border-radius: 10px;
            outline: none;
        }

        .avatar-upload {
            width: 84px;
            height: 84px;
            border: 1px dashed rgba(83, 86, 251, 0.4);
            border-radius: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 14px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: #f8f8ff;
        }

        .avatar-upload .avatar-label {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            position: absolute;
            z-index: 1;
            color: #8b91d2;
        }

        .avatar-upload .avatar-label small {
            font-size: 24px;
        }

        .avatar-upload img {
            width: 100%;
            height: 100%;
            border-radius: 14px;
            display: none;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 2;
        }

        .avatar-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 3;
        }

        .button-group {
            text-align: right;
            margin-top: 14px;
        }

        .button-group button {
            border: none;
            border-radius: 999px;
            padding: 9px 16px;
            cursor: pointer;
            font-weight: 700;
        }

        .confirm-btn {
            color: #fff;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
        }

        .cancel-btn {
            color: #4b556d;
            background: #f1f3ff;
            margin-right: 8px;
        }

        .success-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 10px 16px;
            border-radius: 999px;
            color: #fff;
            background: rgba(28, 34, 61, 0.88);
            font-size: 13px;
            z-index: 1100;
            animation: fadeInOut 2s ease-in-out;
        }

        .success-popup.is-error {
            background: rgba(185, 28, 28, 0.9);
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translate(-50%, -42%); }
            20% { opacity: 1; transform: translate(-50%, -50%); }
            80% { opacity: 1; transform: translate(-50%, -50%); }
            100% { opacity: 0; transform: translate(-50%, -58%); }
        }

        @media (min-width: 992px) {
            .form-section {
                margin-top: 78px;
                padding: 24px;
            }

            .group-list-section {
                margin-top: 16px;
                margin-bottom: 24px;
            }
        }

        @media (max-width: 768px) {
            .form-item,
            .form-group {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .submit {
                width: 100%;
            }

            .group-item {
                grid-template-columns: 1fr;
                align-items: start;
            }

            .group-actions {
                flex-direction: row;
                justify-content: flex-end;
            }
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="moban.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">模版二设置</div>
    </div>
    <!-- 表单区域 -->
    <div class="form-section">
        <div class="panel-head">
            <h2>模板二价格与入口</h2>
            <p>可配置入群金额、底部按钮文案，并维护群聊列表内容用于前台展示。</p>
        </div>
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
            <button type="button" onclick="handleAddClick()">添加</button>
        </div>
        <?php foreach ($groups as $group): ?>
            <div class="group-item" data-id="<?php echo $group['id']; ?>">
                <div class="group-avatar"><img src="<?php echo $group['group_avatar']; ?>" alt="群头像"></div>
                <div class="group-info">
                    <div class="group-name"><?php echo htmlspecialchars($group['nickname']); ?></div>
                    <div class="group-message"><?php echo htmlspecialchars($group['content']); ?></div>
                </div>
                <div class="group-actions">
                    <button type="button" class="edit-button" onclick="handleEditClick(<?php echo $group['id']; ?>)">编辑</button>
                    <button type="button" class="delete-button" onclick="handleDeleteClick(<?php echo $group['id']; ?>)">删除</button>
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
                <button type="button" class="confirm-btn" onclick="handleConfirm()">确认</button>
            </div>
        </div>
    </div>

    <!-- 删除确认弹窗 -->
    <div class="popup" id="delete-confirm-popup">
        <div class="popup-content2">
            <div class="popup-header">确认删除吗？</div>
            <div class="button-group">
                <button type="button" class="cancel-btn" onclick="hideDeleteConfirmPopup()">取消</button>
                <button type="button" class="confirm-btn" onclick="handleDeleteConfirm()">确认删除</button>
            </div>
        </div>
    </div>
	
	<!-- 弹窗容器 -->
	<div id="successPopup" class="success-popup">操作成功</div>

	<script>
	  (function () {
	      const root = document.querySelector('.group-list-section');
	      if (!root || root.dataset.boundTemplate2Settings === '1') {
	          return;
	      }
	      root.dataset.boundTemplate2Settings = '1';

	      const commonPopup = document.getElementById('common-popup');
	      const deletePopup = document.getElementById('delete-confirm-popup');
	      const avatarInput = document.getElementById('avatar-upload');
	      const avatarPreview = document.getElementById('avatar-preview');
	      const plusSign = document.querySelector('.avatar-label small');

	      function showSuccessPopup(message, isError) {
	          const popup = document.getElementById('successPopup');
	          if (!popup) {
	              return;
	          }
	          popup.textContent = message || '操作成功';
	          popup.classList.toggle('is-error', !!isError);
	          popup.style.display = 'block';
	          setTimeout(function () {
	              popup.style.display = 'none';
	              popup.classList.remove('is-error');
	          }, 2200);
	      }

	      function previewAvatar() {
	          if (!avatarInput || !avatarPreview || !plusSign) {
	              return;
	          }
	          if (avatarInput.files && avatarInput.files[0]) {
	              const reader = new FileReader();
	              reader.onload = function (event) {
	                  avatarPreview.src = event.target.result;
	                  avatarPreview.style.display = 'block';
	                  plusSign.style.display = 'none';
	              };
	              reader.readAsDataURL(avatarInput.files[0]);
	              return;
	          }
	          avatarPreview.style.display = 'none';
	          plusSign.style.display = 'block';
	      }

	      function showCommonPopup(title, id) {
	          commonPopup.querySelector('.popup-header').textContent = title;
	          commonPopup.querySelector('input[name="id"]').value = id || '';
	          if (!id) {
	              commonPopup.querySelector('input[name="nickname"]').value = '';
	              commonPopup.querySelector('input[name="content"]').value = '';
	              avatarInput.value = '';
	              avatarPreview.src = '';
	              avatarPreview.style.display = 'none';
	              plusSign.style.display = 'block';
	          }
	          commonPopup.style.display = 'flex';
	      }

	      function hideCommonPopup() {
	          commonPopup.style.display = 'none';
	      }

	      function showDeleteConfirmPopup(id) {
	          deletePopup.style.display = 'flex';
	          deletePopup.setAttribute('data-id', id);
	      }

	      function hideDeleteConfirmPopup() {
	          deletePopup.style.display = 'none';
	      }

	      function handleEditClick(id) {
	          const group = document.querySelector('.group-item[data-id="' + id + '"]');
	          if (!group) {
	              return;
	          }
	          const nickname = group.querySelector('.group-name').textContent;
	          const content = group.querySelector('.group-message').textContent;
	          const avatar = group.querySelector('.group-avatar img').src;

	          commonPopup.querySelector('input[name="nickname"]').value = nickname;
	          commonPopup.querySelector('input[name="content"]').value = content;
	          avatarPreview.src = avatar;
	          avatarPreview.style.display = 'block';
	          plusSign.style.display = 'none';
	          showCommonPopup('编辑群组', id);
	      }

	      function postAction(formData) {
	          return fetch('settings_page2.php', {
	              method: 'POST',
	              headers: {
	                  'X-Requested-With': 'XMLHttpRequest',
	                  'Accept': 'application/json'
	              },
	              body: formData
	          }).then(function (response) {
	              return response.json();
	          });
	      }

	      function handleConfirm() {
	          const nickname = commonPopup.querySelector('input[name="nickname"]').value.trim();
	          const content = commonPopup.querySelector('input[name="content"]').value.trim();
	          const avatar = commonPopup.querySelector('input[name="avatar"]').files[0];
	          const id = commonPopup.querySelector('input[name="id"]').value;

	          if (!nickname || !content) {
	              showSuccessPopup('请先填写群昵称和群消息', true);
	              return;
	          }

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

	          postAction(formData)
	              .then(function (payload) {
	                  if (!payload || !payload.success) {
	                      showSuccessPopup((payload && payload.message) || '操作失败', true);
	                      return;
	                  }
	                  showSuccessPopup(payload.message || '操作成功', false);
	                  hideCommonPopup();
	                  setTimeout(function () {
	                      window.location.reload();
	                  }, 450);
	              })
	              .catch(function (error) {
	                  console.error('操作失败:', error);
	                  showSuccessPopup('操作失败，请稍后重试', true);
	              });
	      }

	      function handleDeleteConfirm() {
	          const id = deletePopup.getAttribute('data-id');
	          if (!id) {
	              showSuccessPopup('删除目标缺失', true);
	              return;
	          }

	          const formData = new FormData();
	          formData.append('action', 'delete');
	          formData.append('id', id);

	          postAction(formData)
	              .then(function (payload) {
	                  if (!payload || !payload.success) {
	                      showSuccessPopup((payload && payload.message) || '删除失败', true);
	                      return;
	                  }
	                  showSuccessPopup(payload.message || '删除成功', false);
	                  hideDeleteConfirmPopup();
	                  setTimeout(function () {
	                      window.location.reload();
	                  }, 450);
	              })
	              .catch(function (error) {
	                  console.error('删除失败:', error);
	                  showSuccessPopup('删除失败，请稍后重试', true);
	              });
	      }

	      window.previewAvatar = previewAvatar;
	      window.showCommonPopup = showCommonPopup;
	      window.hideCommonPopup = hideCommonPopup;
	      window.showDeleteConfirmPopup = showDeleteConfirmPopup;
	      window.hideDeleteConfirmPopup = hideDeleteConfirmPopup;
	      window.handleEditClick = handleEditClick;
	      window.handleDeleteClick = showDeleteConfirmPopup;
	      window.handleAddClick = function () { showCommonPopup('添加群组'); };
	      window.handleConfirm = handleConfirm;
	      window.handleDeleteConfirm = handleDeleteConfirm;

	      <?php if ((isset($operationSuccess) && $operationSuccess) || (isset($updateSuccess) && $updateSuccess)): ?>
	          showSuccessPopup('操作成功', false);
	      <?php endif; ?>
	  })();
	</script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>
     


