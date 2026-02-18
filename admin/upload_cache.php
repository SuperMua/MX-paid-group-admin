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
    </style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="#" onclick="history.length>1 ? history.back() : location.href=document.referrer||'/';"></a>
        <div class="title">清理缓存</div>
    </div>
    <!-- 弹窗1 -->
    <div id="confirmModal" class="custom-modal">
        <div class="modal-content">
            <p>是否清空全部记录？</p>
            <button class="modal-button cancel" onclick="closeConfirmModal()">取消</button>
            <button class="modal-button confirm" onclick="clearAllRecords()">确认</button>
        </div>
    </div>

    <!-- 弹窗2 -->
    <div id="successModal" class="custom-modal">
        <div class="modal-content2">
            <img src="../result/images/ytg.png" alt="">
            <p>操作成功</p>
        </div>
    </div>

    <div class="main" style="margin-top: 50px;"></div>
		<div id="imageInfo" class="imageInfo">
		    <p class="qctx">清空 ../upload 文件夹以释放存储空间</p>
		    <p class="tpdx">图片数量: <span id="imageCount">加载中...</span><span> 张</span></p>
		    <p class="tpdx">图片总大小: <span id="imageSize">加载中...</span></p>
			<p class="tpdx-py">注意<br>1:此文件夹用于存储待审核的图片，清空后文件将无法恢复，请谨慎操作;<br>2:在清空文件夹之前，请确保所有图片已完成审核操作。</p>
			<div class="button-containerhk">
			    <button class="button-qc" onclick="showConfirmModal()">确认清空</button>
			</div>
		</div>
    <script>
        // 页面加载完成后获取图片信息
        window.onload = function() {
            fetch("upload_avatar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "get_image_info=true"
            })
           .then(response => response.json())
           .then(data => {
                document.getElementById("imageCount").textContent = data.count;
                document.getElementById("imageSize").textContent = data.size;
            })
           .catch(error => {
                console.error("Error:", error);
            });
        };

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
            });
        }

        // 关闭成功弹窗
        function closeSuccessModal() {
            document.getElementById("successModal").style.display = "none";
        }
    </script>
</html>