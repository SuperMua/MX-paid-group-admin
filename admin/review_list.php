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
    <title>审核列表</title>
	<style>
	
	</style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php"></a>
        <p class="title">审核列表</p>
    </div>
	<p class="page-intro">按 IP 聚合展示待审与已审记录，进入详情后可进行单条通过/驳回和删除操作。</p>
	<div class="statistics1 order-details">
	    <p>已审核：<span class="order-dingdan"><?php echo $totalApproved;?></span></p>
	    <p>待审核：<span class="order-dingdan"><?php echo $totalPending;?></span></p>
		<p>未通过‌：<span class="order-dingdan"><?php echo $totalRejected;?></span></p>
	</div>
	<button class="more-button" onclick="showConfirmModal()" title="清空审核记录">
	        <img src="../result/images/shanchu.png" alt="">
	   </button>
	   
	   <!-- 弹窗1 -->
	       <div id="confirmModal" class="custom-modal">
	           <div class="modal-content">
	               <p>是否清空全部审核记录？</p>
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
    <div class="container review-list-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="task">
                    <div class="task-left order-details">
                        <div>地区: <span class="order-dingdan"><?php echo htmlspecialchars($row['ip_location']); ?></span></div>
                        <div>时间: <span class="order-dingdan"><?php echo date('Y-m-d H:i:s', strtotime($row['latest_upload_time'])); ?></span></div>
                    </div>
                    <div class="task-right">
                        <div class="task-status <?php echo translate_status(explode(',', $row['statuses'])[0]) == '已通过' ? 'approved' : ''; ?>"><?php echo translate_status(explode(',', $row['statuses'])[0]); ?></div>
                        <div class="task-count">共<?php echo $row['image_count']; ?>张</div>
                    </div>
                    <a href="review_details.php?ip=<?php echo htmlspecialchars($row['ip_address']); ?>" class="view-details">查看详情</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="shuju">没有数据</p>
        <?php endif; ?>
    
	<!-- 分页导航 -->
	<div class="pagination">
	    <?php if ($page > 1): ?>
	        <a href="?page=<?php echo $page - 1; ?>">上一页</a>
	    <?php endif; ?>
	    <?php if ($page < $totalPages): ?>
	        <a href="?page=<?php echo $page + 1; ?>">下一页</a>
	    <?php endif; ?>
	</div>
</div>
<script>
        let isClearProcessing = false;

        // 显示确认弹窗
        function showConfirmModal() {
            if (isClearProcessing) {
                return;
            }
            document.getElementById("confirmModal").style.display = "flex";
            document.getElementById("confirmModal").style.justifyContent = "center";
            document.getElementById("confirmModal").style.alignItems = "center";
            const confirmButton = document.querySelector("#confirmModal .confirm");
            confirmButton.disabled = false;
            confirmButton.textContent = "确认";
        }

        // 关闭确认弹窗
        function closeConfirmModal() {
            document.getElementById("confirmModal").style.display = "none";
        }

        // 执行清空操作
        function clearAllRecords() {
            if (isClearProcessing) {
                return;
            }

            const confirmButton = document.querySelector("#confirmModal .confirm");
            isClearProcessing = true;
            confirmButton.disabled = true;
            confirmButton.textContent = "处理中...";
            closeConfirmModal(); // 关闭确认弹窗
            fetch("review_db.php", {
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
                alert("网络异常，请稍后重试");
            })
            .finally(() => {
                isClearProcessing = false;
                confirmButton.disabled = false;
                confirmButton.textContent = "确认";
            });
        }

        // 关闭成功弹窗
        function closeSuccessModal() {
            document.getElementById("successModal").style.display = "none";
        }
    </script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>


