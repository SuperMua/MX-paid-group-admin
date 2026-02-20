<?php
// 引入数据库配置文件
require_once '../config/config.php';
require_once 'login_check.php'; 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/admin.css">
    <title>清理缓存</title>
    <style>
        .cache-intro {
            font-size: 13px;
            color: #667085;
            line-height: 1.7;
            margin-bottom: 14px;
        }

        .cache-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            border: 1px solid #e9edf3;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #f8fafc;
            font-size: 14px;
            color: #475467;
        }

        .cache-stat strong {
            color: #101828;
        }

        .cache-path {
            display: inline-block;
            margin: 6px 0 4px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #eef4ff;
            border: 1px solid #d6e4ff;
            color: #1d39c4;
            font-size: 12px;
        }

    </style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">清理缓存</div>
    </div>
    <!-- 弹窗1 -->
    <div id="confirmModal" class="custom-modal">
        <div class="modal-content">
            <p>确认清空 upload 目录内的待审图片吗？</p>
            <button class="modal-button cancel" onclick="closeConfirmModal()">取消</button>
            <button class="modal-button confirm" onclick="clearAllRecords()">立即清理</button>
        </div>
    </div>

    <!-- 弹窗2 -->
    <div id="successModal" class="custom-modal">
        <div class="modal-content2">
            <img src="../result/images/ytg.png" alt="">
            <p>操作成功</p>
        </div>
    </div>

    <div class="main" style="margin-top: 50px;">
		<div id="imageInfo" class="imageInfo">
            <p class="qctx">缓存清理会永久删除待审核图片，请在确认业务已完成后再执行。</p>
            <span class="cache-path">目标目录：../upload</span>
            <p class="cache-intro">建议先完成审核与归档，再执行清理操作，避免误删后无法恢复。</p>
		    <div class="cache-stat">
                <span>当前图片数量</span>
                <strong><span id="imageCount">加载中...</span> 张</strong>
            </div>
            <div class="cache-stat">
                <span>当前占用空间</span>
                <strong><span id="imageSize">加载中...</span></strong>
            </div>
			<p class="tpdx-py">注意：清理后文件不可恢复；如有争议订单，请先完成审核再操作。</p>
			<div class="button-containerhk">
			    <button class="button-qc" onclick="showConfirmModal()">立即清理缓存</button>
			</div>
		</div>
	</div>
    <script>
        function loadCacheInfo() {
            fetch("upload_avatar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "get_image_info=true"
            })
           .then(response => response.json())
           .then(data => {
                var countNode = document.getElementById("imageCount");
                var sizeNode = document.getElementById("imageSize");
                if (!countNode || !sizeNode) {
                    return;
                }
                countNode.textContent = data.count;
                sizeNode.textContent = data.size;
            })
           .catch(error => {
                console.error("Error:", error);
                var countNode = document.getElementById("imageCount");
                var sizeNode = document.getElementById("imageSize");
                if (countNode) {
                    countNode.textContent = "--";
                }
                if (sizeNode) {
                    sizeNode.textContent = "加载失败";
                }
            });
        }

        // 显示确认弹窗
        function showConfirmModal() {
            document.getElementById("confirmModal").style.display = "flex";
            document.getElementById("confirmModal").style.justifyContent = "center";
            document.getElementById("confirmModal").style.alignItems = "center";
        }

        // 关闭确认弹窗
        function closeConfirmModal() {
            document.getElementById("confirmModal").style.display = "none";
        }

        // 执行清空操作
        function clearAllRecords() {
            closeConfirmModal(); // 关闭确认弹窗
            fetch("upload_avatar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "clear_all=true"
            })
           .then(response => response.text())
           .then(data => {
                if (data === "操作成功") {
                    document.getElementById("successModal").style.display = "flex";
                    document.getElementById("successModal").style.justifyContent = "center";
                    document.getElementById("successModal").style.alignItems = "center";
                    setTimeout(() => {
                        document.getElementById("successModal").style.display = "none";
                        location.reload(); // 刷新页面
                    }, 2000); // 2秒后关闭弹窗并刷新页面
                } else {
                    alert("操作失败: " + data);
                }
            })
           .catch(error => {
                console.error("Error:", error);
                alert("清理失败，请稍后重试");
            });
        }

        // 关闭成功弹窗
        function closeSuccessModal() {
            document.getElementById("successModal").style.display = "none";
        }

        (function initUploadCachePage() {
            var pageRoot = document.getElementById('imageInfo');
            if (!pageRoot || pageRoot.dataset.boundUploadCachePage === '1') {
                return;
            }
            pageRoot.dataset.boundUploadCachePage = '1';
            loadCacheInfo();
        })();
    </script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>


