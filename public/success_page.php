<?php
require_once 'image_upload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../static/css/upload.css">
    <title>任务审核</title>
    <style>
        .progress-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8); /* 半透明遮罩 */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 1000;
            display: none; /* 默认隐藏进度条 */
        }
        .progress-bar {
            width: 80%;
            height: 6px;
            background-color: #f3f3f3;
            border-radius: 3px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-bar-inner {
            height: 100%;
            background: linear-gradient(270deg, #6afc67, #8cf4f7); /* 精美炫酷的渐变色 */
            border-radius: 3px;
        }
        .progress-text {
            color: #51fb5d;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
		.puty{
			color: #aff5fb;
		}
        .alert {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.7); 
            // border: 1px solid #ddd;
            border-radius: 5px;
            // box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
			padding: 10px;
            display: none;
            text-align: center;
            z-index: 1001; /* 弹窗在进度条上层 */
			font-size: 14px;
			color: #fff;
        }
		
    </style>
</head>
<body>
    <div class="status-container">
        <?php if ($allRejected) { ?>
            <img src="../result/images/wtg.png" alt="审核不通过" class="status-icon">
            <div class="status-text"><h3>审核不通过</h3>
                <p>共上传 <?php echo $totalUploads;?> 张图片</p>
                <p style="color: #159afb;"><a href="upload.php">点击重新上传图片</a></p>
            </div>
        <?php } elseif ($anyPending) { ?>
            <img src="../result/images/shz.png" alt="审核中" class="status-icon">
            <div class="status-text"><h3>审核中</h3>
                <p>共上传 <?php echo $totalUploads;?> 张图片</p>
                <p>提交时间：<?php echo $latestUploadTime;?></p>
            </div>
        <?php } elseif ($hasApproved && !$anyPending) { ?>
            <img src="../result/images/ytg.png" alt="已通过" class="status-icon">
            <div class="status-text"><h3>已通过</h3>
                <p>共上传 <?php echo $totalUploads;?> 张图片</p>
            </div>
        <?php } else { ?>
            <div class="status-text">无记录</div>
        <?php } ?>
    </div>
   
    <!-- 弹窗 -->
    <div class="mask" id="mask3">
        <div class="popup" id="popup3">
            <span class="close-btn" onclick="closePopup3()">×</span>
            <img id="popupImage" src="" alt="入群二维码">
        </div>
    </div>
    <!-- 进度条 -->
    <div class="progress-container" id="progressContainer">
        <div class="progress-text" id="progressText">0%</div>
        <div class="progress-bar">
            <div class="progress-bar-inner" id="progressBarInner"></div>
        </div>
        <div class="progress-text puty">正在审核中，请稍后...</div>
    </div>
    <!-- 自定义提示框 -->
    <div class="alert" id="alertBox">审核通过</div>
   <?php
     // 根据审核状态显示不同的按钮
        if ($allRejected || $anyPending) {
            echo '<a href="#" onclick="history.length>1? history.back() : location.href=document.referrer||\'/\';">';
            echo '<button class="submit-1">返回上一页</button>';
            echo '</a>';
        } elseif ($hasApproved &&!$anyPending) {
            echo '<div class="submit-1">';
            echo '<button type="button" onclick="openPopup3()">查看群二维码</button>';
            echo '</div>';
        }
        $stmt->close();
        $stmtLatest->close();
        $stmtTotal->close();
        $conn->close();
    ?>
    <script src="../static/js/upload.js"></script>
    <script>
        // 检查自动审核开关状态
        let isAutoAuditEnabled = <?php echo $isAutoAuditEnabled; ?>;
        let progressContainer = document.getElementById('progressContainer');
        let progressBarInner = document.getElementById('progressBarInner');
        let progressText = document.getElementById('progressText');
        let alertBox = document.getElementById('alertBox');

        // 如果自动审核开关开启且有图片处于待审核状态，显示进度条
        if (isAutoAuditEnabled && <?php echo $anyPending ? 'true' : 'false'; ?>) {
            progressContainer.style.display = 'flex';

            // 进度条从0%开始，8秒内到达100%
            let startTime = Date.now();
            let interval = setInterval(() => {
                let elapsedTime = Date.now() - startTime;
                let progress = Math.min((elapsedTime / 10000) * 100, 100);
                // 添加顿挫感
                if (progress < 40) {
                    progress = progress; // 正常速度
                } else if (progress < 60) {
                    progress = 40 + (progress - 40) * 0.3; // 慢速
                } else if (progress < 80) {
                    progress = 60 + (progress - 60); // 正常速度
                } else if (progress < 90) {
                    progress = 80 + (progress - 80) * 1; // 加速
                } else {
                    progress = 90 + (progress - 90) * 2; // 加速
                }
                progressBarInner.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';

                if (progress >= 100) {
                    clearInterval(interval);
                    // 进度条到达100%，执行自动更新数据操作
                    updateAuditStatus();
                }
            }, 100); // 每100毫秒更新一次进度条
        }

        function updateAuditStatus() {
            // 模拟数据更新操作
            fetch('update_status.php') // 假设有一个后端脚本用于更新审核状态
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 数据更新完成，显示自定义提示框
                        alertBox.style.display = 'block';
                        // 3秒后隐藏进度条和遮罩，刷新页面
                        setTimeout(() => {
                            progressContainer.style.display = 'none';
                            alertBox.style.display = 'none';
                            window.location.reload();
                        }, 3000); // 3秒后刷新页面
                    } else {
                        alert('审核失败，请稍后重试');
                    }
                })
                .catch(error => {
                    console.error('更新审核状态失败:', error);
                    alert('审核失败，请稍后重试');
                });
        }

        function openPopup3() {
            var token = "<?php echo isset($_SESSION['image_token']) ? $_SESSION['image_token'] : '';?>";
            fetch('new.php?token=' + token)
                .then(res => {
                    if (res.ok) {
                        return res.blob(); // 使用 blob 类型处理图像数据
                    } else {
                        throw new Error('无权访问此图像');
                    }
                })
                .then(blob => {
                    var imageUrl = URL.createObjectURL(blob);
                    document.getElementById('popupImage').src = imageUrl;
                    document.getElementById('mask3').style.display = 'flex';
                    document.getElementById('popup3').style.display = 'block';
                })
                .catch(error => {
                    alert(error.message);
                });
        }

        function closePopup3() {
            document.getElementById('mask3').style.display = 'none';
            document.getElementById('popup3').style.display = 'none';
        }
    </script>
</body>
</html>