<?php
require_once 'review_db.php';  
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/shenhe.css">  
    <link rel="stylesheet" href="../static/css/admin.css">  
    <title>审核详情</title>
    <style>
        .approved {
            color: green;
        }
        .pending{
			color: #ff4d4f;
		}
        .details-empty {
            padding: 16px;
            border: 1px solid #e9edf3;
            border-radius: 10px;
            background: #f8fafc;
            color: #475467;
            line-height: 1.8;
        }
        .details-empty a {
            color: #1677ff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="review_list.php"></a>
        <p class="title">审核详情</p>
    </div>
    <p class="page-intro">点击图片可放大预览，并在底部执行通过/不通过操作；删除操作不可恢复。</p>
    <div class="container review-details-container">
        <?php if ($success):?>
            <div class="success-message" id="successMessage">操作成功</div>
        <?php endif;?>
        <?php if ($error):?>
            <div class="error-message" id="errorMessage">操作失败</div>
        <?php endif;?>
        <?php if (!empty($invalidIp)): ?>
            <div class="details-empty">
                参数异常：未提供有效 IP 地址，无法加载审核详情。<br>
                请从审核列表进入，或点击 <a href="review_list.php">返回审核列表</a>。
            </div>
        <?php elseif ($result && $result->num_rows > 0):?>
            <?php while ($row = $result->fetch_assoc()):?>
                <div class="info order-details">
                    <div class="image-container">
                        <img src="../upload/<?php echo htmlspecialchars($row['filename']);?>" alt="图片预览" class="image-preview" onclick="showPreview(this, '<?php echo urlencode($ip_address);?>', '<?php echo $row['id'];?>')">
                    </div>
                    <div class="parent-klop">
                        <div>状态: <span class="<?php echo strtolower($row['status']);?> order-dingdan"><?php echo translate_status($row['status']);?></span></div>
                        <div>地区: <span class="order-dingdan"><?php echo htmlspecialchars($row['ip_location']); ?></span></div>
                        <div>时间: <span class="order-dingdan"><?php echo htmlspecialchars($row['upload_time']);?></span></div>
                    </div>
                    <div class="info-dopy">
                        <span class="info-conuiejk" data-id="<?php echo $row['id']; ?>">删除</span>
                    </div>
                </div>
            <?php endwhile;?>
        <?php else:?>
            <div class="details-empty">
                当前 IP 暂无审核数据，可能已清空或尚未上传。<br>
                可点击 <a href="review_list.php">返回审核列表</a> 继续处理其他记录。
            </div>
        <?php endif;?>
    </div>
    <!-- 图片弹窗 -->
    <div class="image-preview-container" id="imagePreviewContainer" style="display: none;">
        <img id="previewImage" src="" alt="图片预览">
        <div class="close-button" onclick="hidePreview()">&times;</div>
    </div>
    
    <!-- 底部按钮 -->
    <div class="floating-buttons" id="floatingButtons" style="display: none;">
        <a id="rejectButton" href="#" class="action-btn reject">不通过</a>
		<a id="approveButton" href="#" class="action-btn approve">通过</a>
    </div>

    <!-- 弹窗1 -->
    <div id="confirmModal" class="custom-modal"  data-current-id="">
        <div class="modal-content">
            <p>是否删除记录？</p>
            <button class="modal-button cancel" onclick="closeConfirmModal()">取消</button>
			<button class="modal-button confirm">确认</button>
        </div>
    </div>

    <!-- 弹窗2 -->
    <div id="successModal" class="custom-modal">
        <div class="modal-content2">
            <img src="../result/images/ytg.png" alt="">
            <p>操作成功</p>
        </div>
    </div>

    <script>
        var isDeleteProcessing = false;

        function showPreview(img, ip, imageId) {
            const previewContainer = document.getElementById('imagePreviewContainer');  
            const previewImage = document.getElementById('previewImage');  
            const approveButton = document.getElementById('approveButton');  
            const rejectButton = document.getElementById('rejectButton');  
            const floatingButtons = document.getElementById('floatingButtons'); // 获取按钮容器
    
            previewImage.src = img.src;  
            previewContainer.style.display = 'flex';
    
            // 设置底部按钮的链接
            approveButton.href = `?ip=${ip}&action=approve&image_id=${imageId}`;
            rejectButton.href = `?ip=${ip}&action=reject&image_id=${imageId}`;
    
            // 显示底部按钮
            floatingButtons.style.display = 'flex'; // 同时显示按钮
        }
    
        function hidePreview() {
            const previewContainer = document.getElementById('imagePreviewContainer');  
            const floatingButtons = document.getElementById('floatingButtons'); // 获取按钮容器
    
            previewContainer.style.display = 'none';
            floatingButtons.style.display = 'none'; // 同时隐藏按钮
        }
    
        function showActionFeedback() {
            const successMessage = document.getElementById('successMessage');  
            const errorMessage = document.getElementById('errorMessage');  
            if (successMessage) {
                successMessage.style.display = 'block'; // 显示提示信息
                setTimeout(function() {
                    successMessage.style.display = 'none'; // 3 秒后隐藏提示信息
                }, 2000);
            }
            if (errorMessage) {
                errorMessage.style.display = 'block'; // 显示提示信息
                setTimeout(function() {
                    errorMessage.style.display = 'none'; // 2 秒后隐藏提示信息
                }, 2000);
            }
        }
    
        // 显示确认弹窗并传递当前记录的ID
        function showConfirmModal(id) {
            document.getElementById("confirmModal").dataset.currentId = id;  // 存储当前操作的ID 
            document.getElementById("confirmModal").style.display = "flex";
            document.getElementById("confirmModal").style.justifyContent = "center";
            document.getElementById("confirmModal").style.alignItems = "center";
        }
    
        function bindReviewDetailsEvents() {
            // 绑定所有删除按钮点击事件 
            document.querySelectorAll('.info-conuiejk').forEach(button => {
                button.addEventListener('click', function(e) {
                    const id = this.dataset.id;
                    showConfirmModal(id);
                });
            });
    
            // 绑定模态框确认按钮事件 
            const confirmButton = document.querySelector('#confirmModal .confirm');
            if (!confirmButton) {
                return;
            }
            confirmButton.addEventListener('click', function() {
                const id = document.getElementById("confirmModal").dataset.currentId; 
                closeConfirmModal();
                if(id) deleteSingleRecord(id);
            });
        }
    
        // 执行删除操作
        function deleteSingleRecord(id) {
            if (isDeleteProcessing) {
                return;
            }
            if (!id) {
                alert("未获取到要删除的记录编号，请刷新后重试");
                return;
            }
            const confirmButton = document.querySelector('#confirmModal .confirm');
            isDeleteProcessing = true;
            confirmButton.disabled = true;
            confirmButton.textContent = '处理中...';
            fetch("review_db.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=delete_single&id=" + encodeURIComponent(id) 
            })
            .then(response => response.text()) 
            .then(data => {
                // 去除 HTML 标签
                const cleanData = data.replace(/<[^>]*>/g, '').trim();
                if (cleanData === "操作成功") { 
                    document.getElementById("successModal").style.display = "flex";
                    document.getElementById("successModal").style.justifyContent = "center";
                    document.getElementById("successModal").style.alignItems = "center";
                    setTimeout(() => {
                        document.getElementById("successModal").style.display = "none";
                        location.reload();  // 刷新页面 
                    }, 2000);
                } else {
                    alert("删除失败: " + cleanData);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("网络异常，请稍后重试");
            })
            .finally(() => {
                isDeleteProcessing = false;
                confirmButton.disabled = false;
                confirmButton.textContent = '确认';
            });
        }
    
        // 关闭弹窗函数 
        function closeConfirmModal() {
            document.getElementById("confirmModal").style.display = "none";
        }

        (function initReviewDetailsPage() {
            const pageRoot = document.querySelector('.main');
            if (!pageRoot || pageRoot.dataset.boundReviewDetailsPage === '1') {
                return;
            }
            pageRoot.dataset.boundReviewDetailsPage = '1';
            showActionFeedback();
            bindReviewDetailsEvents();
        })();
    </script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>


