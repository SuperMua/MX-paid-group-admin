<?php
// 在文件最开头添加严格的输出控制
if (ob_get_level()) ob_end_clean();
ob_start();

// 关闭所有错误显示，但记录到日志
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'login_check.php'; 
require_once '../config/config.php';

// 处理图片上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['type'])) {
    // 确保没有任何输出
    if (ob_get_length()) ob_clean();
    
    header('Content-Type: application/json; charset=utf-8');

    $allowedTypes = ['qr_code', 'customer_service_image'];
    $type = $_POST['type'];
    
    if (!in_array($type, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => '无效的类型']);
        exit;
    }

    $file = $_FILES['image'];
    
    // 验证文件错误
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => '文件大小超过服务器限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => 'PHP扩展阻止了文件上传'
        ];
        $message = isset($errorMessages[$file['error']]) ? $errorMessages[$file['error']] : '未知错误: ' . $file['error'];
        echo json_encode(['success' => false, 'message' => '文件上传错误: ' . $message]);
        exit;
    }

    // 验证文件类型和大小
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['size'] > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => '文件大小不能超过5MB']);
        exit;
    }
    
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        echo json_encode(['success' => false, 'message' => '上传的文件不是有效的图片']);
        exit;
    }
    $mime = $image_info['mime'];
    if (!in_array($mime, $allowedMime)) {
        echo json_encode(['success' => false, 'message' => '仅支持JPEG、PNG、GIF格式']);
        exit;
    }

    // 使用相对路径，确保路径正确
    $uploadDir = '../static/images/';
    
    // 检查目录是否存在，如果不存在则尝试创建
    if (!is_dir($uploadDir)) {
        // 尝试创建目录，使用755权限
        if (!mkdir($uploadDir, 0755, true)) {
            // 如果创建失败，尝试使用系统临时目录作为备选
            $uploadDir = sys_get_temp_dir() . '/app_images/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                echo json_encode(['success' => false, 'message' => '无法创建上传目录']);
                exit;
            }
        }
    }
    
    // 检查目录是否可写
    if (!is_writable($uploadDir)) {
        // 尝试使用系统临时目录作为备选方案
        $fallbackDir = sys_get_temp_dir() . '/app_images/';
        if (!is_dir($fallbackDir)) {
            mkdir($fallbackDir, 0755, true);
        }
        
        if (is_writable($fallbackDir)) {
            $uploadDir = $fallbackDir;
        } else {
            echo json_encode(['success' => false, 'message' => '上传目录不可写']);
            exit;
        }
    }
    
    // 生成安全的文件名
    $originalName = basename($file['name']);
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $safeExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $safeExtensions)) {
        echo json_encode(['success' => false, 'message' => '不支持的文件类型']);
        exit;
    }
    
    $newName = uniqid() . '.' . $fileExtension;
    $filePath = $uploadDir . $newName;

    // 尝试移动上传的文件
    if (!@move_uploaded_file($file['tmp_name'], $filePath)) {
        // 如果move_uploaded_file失败，尝试使用copy
        if (!@copy($file['tmp_name'], $filePath)) {
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : '未知错误';
            echo json_encode(['success' => false, 'message' => '文件保存失败: ' . $errorMsg]);
            exit;
        }
    }

    // 验证文件确实已创建且可读
    if (!file_exists($filePath) || !is_readable($filePath)) {
        echo json_encode(['success' => false, 'message' => '文件上传后验证失败']);
        exit;
    }

    try {
        // 更新数据库 - 使用相对路径存储
        $relativePath = '../static/images/' . $newName;
        $sql = "UPDATE settings SET `$type` = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            // 如果数据库更新失败，删除已上传的文件
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw new Exception("预处理失败: " . ($conn->error ? $conn->error : '未知错误'));
        }
        
        $id = 1;
        $stmt->bind_param('si', $relativePath, $id);
        
        if (!$stmt->execute()) {
            // 如果数据库更新失败，删除已上传的文件
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw new Exception("执行失败: " . $stmt->error);
        }

        $response = [
            'success' => true,
            'message' => '替换成功',
            'newPath' => $relativePath
        ];
        
        $json_output = json_encode($response, JSON_UNESCAPED_SLASHES);
        if ($json_output === false) {
            throw new Exception("JSON编码失败: " . json_last_error_msg());
        }
        
        echo $json_output;
        
    } catch (Exception $e) {
        if (ob_get_length()) ob_clean();
        
        http_response_code(500);
        $error_response = [
            'success' => false,
            'message' => '服务器错误: ' . $e->getMessage()
        ];
        
        $json_error = json_encode($error_response, JSON_UNESCAPED_SLASHES);
        if ($json_error === false) {
            die('{"success":false,"message":"服务器错误"}');
        }
        
        echo $json_error;
    }
    exit;
}

// 非POST请求的处理
if (ob_get_length()) ob_clean();

// 获取当前设置
$result = $conn->query("SELECT qr_code, customer_service_image FROM settings LIMIT 1");
if ($result) {
    $current = $result->fetch_assoc();
} else {
    $current = ['qr_code' => '', 'customer_service_image' => ''];
}

// 关闭数据库连接
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../static/css/admin.css">
    <title>模板设置</title>
    <style>
        body {
            margin: 0;
            background:
                radial-gradient(1200px 460px at 0% 0%, rgba(83, 86, 251, 0.14), transparent 55%),
                radial-gradient(900px 420px at 100% 0%, rgba(245, 57, 248, 0.1), transparent 50%),
                #f5f7ff;
        }

        .container {
            width: min(1180px, calc(100% - 24px));
            margin: 68px auto 24px;
            background: #fff;
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 34px rgba(83, 86, 251, 0.12);
        }

        .template-settings-head {
            padding: 18px 18px 12px;
            border-bottom: 1px solid rgba(83, 86, 251, 0.12);
        }

        .template-settings-head h2 {
            margin: 0 0 8px;
            color: #1f2a47;
            font-size: 22px;
        }

        .template-settings-head p {
            margin: 0;
            font-size: 13px;
            line-height: 1.7;
            color: #667085;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            padding: 16px;
            border-bottom: 1px solid rgba(83, 86, 251, 0.12);
        }

        .row:last-child {
            border-bottom: none;
        }

        .template,
        .qr-code {
            flex: 1 1 280px;
            min-width: 0;
            border: 1px solid rgba(20, 28, 48, 0.12);
            border-radius: 14px;
            padding: 12px;
            background: #f9fafe;
        }

        .template h3,
        .qr-code h3 {
            margin: 0 0 10px;
            font-size: 15px;
            color: #3c4871;
        }

        .image-placeholder {
            width: 100%;
            height: 220px;
            border-radius: 12px;
            background: transparent;
            border: 1px solid rgba(20, 28, 48, 0.14);
            overflow: hidden;
        }

        .image-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-placeholder-template {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background: transparent;
        }

        .template .image-placeholder {
            height: 326px;
        }

        .qr-code .image-placeholder {
            height: 326px;
        }

        .iphone17-pro {
            position: relative;
            width: min(206px, 90%);
            aspect-ratio: 9 / 19.5;
            border-radius: 42px;
            padding: 6px;
            background: linear-gradient(145deg, #a2a8b1 0%, #696f79 28%, #9da3ad 58%, #5e646e 100%);
            box-shadow:
                0 24px 36px rgba(18, 24, 41, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.32),
                inset 0 -1px 0 rgba(0, 0, 0, 0.34);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .iphone17-pro::before {
            content: "";
            position: absolute;
            inset: 3px;
            border-radius: 38px;
            border: 1px solid rgba(255, 255, 255, 0.34);
            pointer-events: none;
        }

        .iphone17-pro::after {
            content: "";
            position: absolute;
            right: -2px;
            top: 146px;
            width: 3px;
            height: 86px;
            border-radius: 999px;
            background: linear-gradient(180deg, #aeb4be 0%, #5f6670 100%);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        .iphone17-pro-btn {
            position: absolute;
            left: -2px;
            width: 3px;
            border-radius: 999px;
            background: linear-gradient(180deg, #afb5bf 0%, #66707b 100%);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        .iphone17-pro-btn-action {
            top: 104px;
            height: 28px;
        }

        .iphone17-pro-btn-volume-up {
            top: 144px;
            height: 58px;
        }

        .iphone17-pro-btn-volume-down {
            top: 214px;
            height: 58px;
        }

        .iphone17-pro-screen {
            width: 100%;
            height: 100%;
            border-radius: 35px;
            overflow: hidden;
            background: #0a0a0a;
            border: 1px solid rgba(255, 255, 255, 0.09);
        }

        .iphone17-pro-screen img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center top;
            display: block;
            background: #10131a;
        }

        .iphone17-pro-island {
            position: absolute;
            top: 13px;
            left: 50%;
            transform: translateX(-50%);
            width: 108px;
            height: 28px;
            border-radius: 999px;
            background: #020203;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.08),
                0 3px 8px rgba(0, 0, 0, 0.34);
            pointer-events: none;
        }

        .iphone17-pro-indicator {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            width: 92px;
            height: 4px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.52);
            pointer-events: none;
        }

        .image-placeholder-qr {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            background: #fff;
        }

        .image-placeholder-qr img {
            width: auto !important;
            height: auto !important;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain !important;
            object-position: center !important;
            display: block;
            background: transparent;
        }

        .buttons {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .button {
            width: 100%;
            min-height: 46px;
            border: none;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
            writing-mode: horizontal-tb;
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        a.button {
            text-decoration: none;
        }

        .button:hover {
            transform: translateY(-1px);
            filter: saturate(1.05);
        }

        .button.preview,
        .button.swap {
            color: #fff;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            box-shadow: 0 12px 22px rgba(83, 86, 251, 0.24);
        }

        .button.edit {
            color: #4a4fd7;
            background: #eef0ff;
            border: 1px solid rgba(83, 86, 251, 0.3);
            box-shadow: 0 10px 18px rgba(83, 86, 251, 0.14);
        }

        .button.swap {
            margin-top: 12px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(10, 13, 32, 0.66);
            z-index: 1000;
        }

        .modal-content {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: min(1200px, calc(100% - 24px));
            height: min(86vh, 900px);
            background-color: #eef3ff;
            border-radius: 16px;
            overflow: hidden;
            z-index: 1001;
            border: 1px solid rgba(83, 86, 251, 0.22);
            box-shadow: 0 24px 44px rgba(9, 20, 40, 0.34);
        }

        .link-generator {
            display: flex;
            gap: 10px;
            padding: 12px 56px 0 14px;
        }

        #generatedLink {
            flex: 1;
            padding: 9px 11px;
            border: 1px solid #cfd4ff;
            border-radius: 10px;
            font-size: 13px;
        }

        #copyBtn {
            padding: 0 16px;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
        }

        .modal-body {
            height: calc(100% - 58px);
            padding: 8px 14px 12px;
        }

        .modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 12px;
            background: #fff;
        }

        .close-button {
            position: absolute;
            top: 4px;
            right: 14px;
            color: #4d5780;
            cursor: pointer;
            font-size: 30px;
            line-height: 1;
        }

        .notification-area {
            position: absolute;
            top: 10px;
            left: 0;
            right: 0;
            text-align: center;
            pointer-events: none;
            z-index: 1002;
        }

        .notification-message {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            color: #fff;
            background: rgba(25, 137, 57, 0.92);
            box-shadow: 0 8px 16px rgba(12, 52, 21, 0.3);
            animation: fadeInOut 2.4s ease-in-out;
        }

        #toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(32, 37, 65, 0.9);
            color: #fff;
            font-size: 13px;
            z-index: 1200;
        }

        #toast.is-visible {
            display: block;
            animation: toastFade 2s ease;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-8px); }
            20% { opacity: 1; transform: translateY(0); }
            80% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-8px); }
        }

        @keyframes toastFade {
            0% { opacity: 0; transform: translate(-50%, -42%); }
            15% { opacity: 1; transform: translate(-50%, -50%); }
            85% { opacity: 1; transform: translate(-50%, -50%); }
            100% { opacity: 0; transform: translate(-50%, -58%); }
        }

        @media (min-width: 992px) {
            .container {
                margin-top: 78px;
            }

            .template .image-placeholder {
                height: 372px;
            }

            .qr-code .image-placeholder {
                height: 350px;
            }

            .iphone17-pro {
                width: min(232px, 86%);
            }
        }

        @media (max-width: 768px) {
            .link-generator {
                flex-direction: column;
                padding-right: 14px;
            }

            #copyBtn {
                height: 38px;
            }
        }
    </style>
</head>
<body>
<div class="navbar">
    <a class="back-button left-arrow" href="settings.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
    <div class="title">模板设置</div>
</div>
<div class="container">
    <div class="template-settings-head">
        <h2>模板与二维码资产</h2>
        <p>这里可以管理模板预览、群二维码与客服二维码。点击“预览”可实时查看前台页面效果。</p>
    </div>
    <div class="row">
        <div class="template">
            <h3>模板一</h3>
            <div class="image-placeholder image-placeholder-template">
                <div class="iphone17-pro">
                    <span class="iphone17-pro-btn iphone17-pro-btn-action"></span>
                    <span class="iphone17-pro-btn iphone17-pro-btn-volume-up"></span>
                    <span class="iphone17-pro-btn iphone17-pro-btn-volume-down"></span>
                    <span class="iphone17-pro-island"></span>
                    <div class="iphone17-pro-screen">
                        <img src="../result/images/mb1_screen.png" alt="模板一缩略图">
                    </div>
                    <span class="iphone17-pro-indicator"></span>
                </div>
            </div>
            <div class="buttons">
                <a class="button edit" href="settings_page.php">编辑模板</a>
                <button type="button" class="button preview" id="preview-v1">预览效果</button>
            </div>
        </div>
        <div class="template">
            <h3>模板二</h3>
            <div class="image-placeholder image-placeholder-template">
                <div class="iphone17-pro">
                    <span class="iphone17-pro-btn iphone17-pro-btn-action"></span>
                    <span class="iphone17-pro-btn iphone17-pro-btn-volume-up"></span>
                    <span class="iphone17-pro-btn iphone17-pro-btn-volume-down"></span>
                    <span class="iphone17-pro-island"></span>
                    <div class="iphone17-pro-screen">
                        <img src="../result/images/mb2_screen.png" alt="模板二缩略图">
                    </div>
                    <span class="iphone17-pro-indicator"></span>
                </div>
            </div>
            <div class="buttons">
                <a class="button edit" href="settings_page2.php">编辑模板</a>
                <button type="button" class="button preview" id="preview-v2">预览效果</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="qr-code">
            <h3>入群二维码</h3>
            <div class="image-placeholder image-placeholder-qr">
			    <img src="<?= htmlspecialchars($current['qr_code']) ?>" alt="入群二维码">
			</div>
           <form class="upload-form" enctype="multipart/form-data">
               <input type="hidden" name="type" value="qr_code">
               <input type="file" name="image" accept="image/*" hidden>
               <button type="button" class="button swap">替换</button>
           </form>
        </div>
        <div class="qr-code">
            <h3>客服二维码</h3>
            <div class="image-placeholder image-placeholder-qr">
                  <img src="<?= htmlspecialchars($current['customer_service_image']) ?>" alt="客服二维码">
			</div>
            <form class="upload-form" enctype="multipart/form-data">
                <input type="hidden" name="type" value="customer_service_image">
                <input type="file" name="image" accept="image/*" hidden>
                <button type="button" class="button swap">替换</button>
            </form>
        </div>
    </div>
</div>

<!-- 弹窗和遮罩 -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal-content" id="modalContent">
<div id="notificationArea" class="notification-area"></div>
    <div class="modal-header">
        <!-- 链接生成区域 -->
        <div class="link-generator" id="linkGenerator">
            <input type="text" id="generatedLink" readonly>
            <button id="copyBtn">复制链接</button>
            <span id="copySuccess"></span>
        </div>
        <span class="close-button" id="closeButton">×</span>
    </div>
	<div class="modal-body" id="modalBody"></div>
</div>

<!-- 新增成功提示弹窗 -->
<div id="toast">
    <span id="toastMessage"></span>
</div>
<script>
   (function () {
       const container = document.querySelector('.container');
       if (!container || container.dataset.boundTemplateSettings === '1') {
           return;
       }
       container.dataset.boundTemplateSettings = '1';

       const modalOverlay = document.getElementById('modalOverlay');
       const modalContent = document.getElementById('modalContent');
       const modalBody = document.getElementById('modalBody');
       const closeButton = document.getElementById('closeButton');
       const linkGenerator = document.getElementById('linkGenerator');
       const generatedLink = document.getElementById('generatedLink');
       const copyBtn = document.getElementById('copyBtn');
       const notificationArea = document.getElementById('notificationArea');
       const toast = document.getElementById('toast');

       function showToast(message) {
           document.getElementById('toastMessage').textContent = message;
           toast.classList.remove('is-visible');
           void toast.offsetWidth;
           toast.classList.add('is-visible');
           setTimeout(() => {
               toast.classList.remove('is-visible');
           }, 2000);
       }

       function closePreviewModal() {
           modalOverlay.style.display = 'none';
           modalContent.style.display = 'none';
           modalBody.innerHTML = '';
       }

       function openPreview(versionId) {
           const currentDomain = window.location.origin;
           const previewPath = versionId === 'v2' ? '/public/home_v2.php' : '/public/home_v1.php';
           const previewUrl = `${currentDomain}${previewPath}`;

           modalOverlay.style.display = 'block';
           modalContent.style.display = 'block';
           modalBody.innerHTML = `<iframe src="${previewUrl}" style="width:100%;height:100%;border:none;"></iframe>`;
           generatedLink.value = previewUrl;
           linkGenerator.style.display = 'flex';
       }

       function appendCopyNotification() {
           const notification = document.createElement('div');
           notification.className = 'notification-message';
           notification.textContent = '✓ 链接已复制';
           notificationArea.innerHTML = '';
           notificationArea.appendChild(notification);
           setTimeout(() => notification.remove(), 2400);
       }

       document.querySelectorAll('.button.preview').forEach(button => {
           button.addEventListener('click', () => {
               const versionId = button.id.includes('v2') ? 'v2' : 'v1';
               openPreview(versionId);
           });
       });

       copyBtn.addEventListener('click', () => {
           const text = generatedLink.value;
           if (!text) {
               return;
           }

           if (navigator.clipboard && navigator.clipboard.writeText) {
               navigator.clipboard.writeText(text).then(appendCopyNotification).catch(() => {
                   generatedLink.select();
                   document.execCommand('copy');
                   appendCopyNotification();
               });
               return;
           }

           generatedLink.select();
           document.execCommand('copy');
           appendCopyNotification();
       });

       closeButton.addEventListener('click', closePreviewModal);
       modalOverlay.addEventListener('click', closePreviewModal);

       document.querySelectorAll('.swap').forEach(button => {
           button.addEventListener('click', function () {
               const form = this.closest('form');
               const fileInput = form ? form.querySelector('input[type="file"]') : null;
               if (fileInput) {
                   fileInput.click();
               }
           });
       });

       document.querySelectorAll('input[type="file"]').forEach(input => {
           input.addEventListener('change', function () {
               const form = this.closest('form');
               if (!form) {
                   return;
               }

               const formData = new FormData(form);
               fetch('moban.php', {
                   method: 'POST',
                   body: formData
               })
                   .then(response => {
                       if (!response.ok) {
                           throw new Error(`HTTP错误 ${response.status}`);
                       }
                       return response.json();
                   })
                   .then(res => {
                       if (!res.success) {
                           alert(res.message || '操作失败');
                           return;
                       }

                       const imageContainer = form.previousElementSibling;
                       const image = imageContainer ? imageContainer.querySelector('img') : null;
                       if (image) {
                           image.src = res.newPath + '?t=' + Date.now();
                       }
                       showToast(res.message || '替换成功');
                   })
                   .catch(err => {
                       console.error('请求失败:', err);
                       alert(`请求失败: ${err.message}`);
                   });
           });
       });
   })();
</script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>


