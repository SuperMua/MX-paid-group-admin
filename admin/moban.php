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
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 95%;
            max-width: 500px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .row {
            display: flex;
            justify-content: space-between;
			gap: 16px;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .template, .qr-code {
            width: 45%;
        }
        .template h3, .qr-code h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
			margin-left: 10px;
        }
        .image-placeholder {
            width: 100%;
            height: 220px;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin-top: 10px;
        }
        .image-placeholder img {
            width: 100%;
            height: 220px;
            border-radius: 5px;
			border: 1px solid #ccc;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }
        .button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .button.edit {
            background-color: #4CAF50;
            color: white;
        }
        .button.preview {
            background-color: #2196F3;
            color: white;
        }
        .button.swap {
            background-color: #2196F3;
            color: white;
            width: 100%;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        /* 弹窗和遮罩样式 */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }
        /*.modal-header {
            position: relative;
            padding: 10px;
            background-color: #f0f0f0;
        }*/
        .modal-body {
            height: calc(100% - 30px);
            overflow: auto;
        }
        .modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
			margin-top: 15px;
        }
		/* 添加成功提示样式 */
            /* 弹窗和遮罩样式 */
            .modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                z-index: 1000;
            }
            .modal-content {
                display: none; 
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 90%;
                max-width: 500px;
                height: 80%;
                background-color: #eef3ff;
                border-radius: 8px 8px 0 0;
                overflow: hidden;
                z-index: 1001;
            }
            .close-button {
                position: absolute;
                top: 0px;
                right: 12px;
                /*width: 50px;
                height: 28px;
                border-radius: 5px;
                background-color: #bfbfbf;*/
                color: #666;;
                border: none;
                cursor: pointer;
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 30px;
            }
            .modal-body {
                height: calc(100% - 30px);
                overflow: auto;
            }
            /* 优化后的弹窗提示样式 */
            #toast {
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: rgba(0, 0, 0, 0.5);
                color: white;
                padding: 10px 20px;
                border-radius: 4px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                font-size: 16px;
                text-align: center;
                white-space: nowrap;
                animation: showToast 2s forwards;
				font-size: 14px;
            }
            /* 弹窗提示动画 */
            @keyframes showToast {
                0% { opacity: 0; transform: translate(-50%, -60%); }
                10% { opacity: 1; }
                90% { opacity: 1; }
                100% { opacity: 0; transform: translate(-50%, -40%); }
            }
		/* 链接生成器样式 */
		.link-generator {
		    display: flex;
		    gap: 10px;
		    flex-grow: 1;
			margin-top: 20px;
		}
		
		#generatedLink {
		    flex: 1;
		    padding: 8px;
		    border: 1px solid #ddd;
		    border-radius: 4px;
		}
		
		#copyBtn {
		    padding: 8px 15px;
		    background: #4285f4;
		    color: white;
		    border: none;
		    border-radius: 4px;
		    cursor: pointer;
		}
		
		/* 新增通知区域样式 */
		.notification-area {
		    position: absolute;
		    top: 10px;
		    left: 0;
		    right: 0;
		    text-align: center;
		    pointer-events: none; /* 防止阻挡点击 */
		    z-index: 1002; /* 确保在弹窗上方 */
		}
		
		/* 修改成功提示样式 */
		.notification-message {
		    display: inline-block;
		    padding: 8px 16px;
		    background: rgba(76, 175, 80, 0.9);
		    color: white;
		    border-radius: 4px;
		    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
		    animation: fadeInOut 2.5s ease-in-out;
		    opacity: 0;
		}
		
		@keyframes fadeInOut {
		    0% { opacity: 0; transform: translateY(-20px); }
		    20% { opacity: 1; transform: translateY(0); }
		    80% { opacity: 1; transform: translateY(0); }
		    100% { opacity: 0; transform: translateY(-20px); }
		}

		@media (min-width: 992px) {
			body {
				background: #fafbfc;
				padding: 0;
			}

			.container {
				width: min(1180px, calc(100% - 64px));
				max-width: none;
				margin-top: 76px;
				border: 1px solid rgba(0, 0, 0, 0.08);
				border-radius: 12px;
				box-shadow: 0 10px 24px rgba(16, 24, 40, 0.05);
			}

			.row {
				padding: 20px;
			}

			.template,
			.qr-code {
				width: calc(50% - 8px);
			}

			.image-placeholder,
			.image-placeholder img {
				height: 280px;
			}

			.button {
				padding: 8px 16px;
			}

			.modal-content {
				width: min(1180px, calc(100% - 64px));
				max-width: none;
				height: 86%;
				border-radius: 12px;
			}

			.modal-body iframe {
				margin-top: 12px;
				border-radius: 8px;
			}
		}

		
    </style>
</head>
<body>
<div class="navbar">
    <a class="back-button left-arrow" href="settings.php"></a>
    <div class="title">模板设置</div>
</div>
<div class="container">
    <div class="row">
        <div class="template">
            <h3>模板一</h3>
            <div class="image-placeholder"><img src="../result/images/mb1.png" alt="" /></div>
            <div class="buttons">
                <a href="settings_page.php"><button class="button edit">编辑</button></a>
                <button class="button preview" id="preview-v1">预览</button>
            </div>
        </div>
        <div class="template">
            <h3>模板二</h3>
            <div class="image-placeholder"><img src="../result/images/mb2.png" alt="" /></div>
            <div class="buttons">
                <a href="settings_page2.php"><button class="button edit">编辑</button></a>
                <button class="button preview" id="preview-v2">预览</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="qr-code">
            <h3>入群二维码</h3>
            <div class="image-placeholder">
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
            <div class="image-placeholder">
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
   // 确保DOM完全加载后执行
   document.addEventListener('DOMContentLoaded', function() {
       const previewButtons = document.querySelectorAll('.button.preview');
       const modalOverlay = document.getElementById('modalOverlay');
       const modalContent = document.getElementById('modalContent');
       const modalBody = document.getElementById('modalBody');
       const closeButton = document.getElementById('closeButton');
       const linkGenerator = document.getElementById('linkGenerator');
       const generatedLink = document.getElementById('generatedLink');
       const copyBtn = document.getElementById('copyBtn');
       const copySuccess = document.getElementById('copySuccess');
   
       previewButtons.forEach(button => {
           button.addEventListener('click', () => {
               // 获取当前域名
               const currentDomain = window.location.origin;
               
               // 判断是V1还是V2按钮（通过按钮文字或ID判断）
               const isV1 = button.id.includes('v1');
               const isV2 = button.id.includes('v2');
               
               // 生成对应的URL
               let previewUrl, shareUrl;
               if(isV1) {
                   previewUrl = `${currentDomain}/public/home_v1.php`;
                   shareUrl = `${currentDomain}/public/home_v1.php`;
               } else if(isV2) {
                   previewUrl = `${currentDomain}/public/home_v2.php`;
                   shareUrl = `${currentDomain}/public/home_v2.php`;
               } else {
                   console.error('无法确定模板版本');
                   return;
               }
               
               //console.log('生成的预览URL:', previewUrl);
               //console.log('生成的分享URL:', shareUrl);
               
               // 显示弹窗
               modalOverlay.style.display = 'block';
               modalContent.style.display = 'block';
               
               // 加载iframe
               modalBody.innerHTML = `<iframe src="${previewUrl}" style="width:100%;height:100%;border:none;"></iframe>`;
               
               // 设置可复制的分享链接
               generatedLink.value = shareUrl;
               linkGenerator.style.display = 'flex';
           });
       });
   
       copyBtn.addEventListener('click', () => {
           generatedLink.select();
           document.execCommand('copy');
           
           // 创建通知元素
           const notification = document.createElement('div');
           notification.className = 'notification-message';
           notification.textContent = '✓ 链接已复制';
           
           // 添加到通知区域
           const notificationArea = document.getElementById('notificationArea');
           notificationArea.innerHTML = '';
           notificationArea.appendChild(notification);
           
           // 3秒后自动移除
           setTimeout(() => {
               notification.remove();
           }, 2500);
       });
   
       closeButton.addEventListener('click', () => {
           modalOverlay.style.display = 'none';
           modalContent.style.display = 'none';
       });
   
       modalOverlay.addEventListener('click', () => {
           modalOverlay.style.display = 'none';
           modalContent.style.display = 'none';
       });
   });

// 文件上传处理
document.querySelectorAll('.swap').forEach(button => {
    button.addEventListener('click', function() {
        const form = this.closest('form');
        const fileInput = form.querySelector('input[type="file"]');
        fileInput.click();
    });
});

document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const form = this.closest('form');
        const formData = new FormData(form);
        
        fetch('moban.php', { // 确保URL正确
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
            if (res.success) {
                const img = form.previousElementSibling.querySelector('img');
                img.src = res.newPath + '?t=' + Date.now();
                showToast(res.message);
            } else {
                alert(res.message || '操作失败');
            }
        })
        .catch(err => {
            console.error('请求失败:', err);
            alert(`请求失败: ${err.message}`);
        });
    });
});

function showToast(message) {
    const toast = document.getElementById('toast');
    toast.style.display = 'block';
    toast.style.animation = 'none';
    toast.offsetHeight; // 触发重绘
    document.getElementById('toastMessage').textContent = message;
    toast.style.animation = 'slideIn 0.5s, fadeOut 0.5s 1.5s';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 2000);
}
</script>
</body>
</html>
