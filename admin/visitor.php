<?php
require_once 'query-visitors.php'; 
require_once 'login_check.php'; 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <link rel="stylesheet" href="../static/css/admin.css">
    <title>访客记录</title>
	<style>	
	</style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php"></a>
        <div class="title">访客记录</div>
    </div>
   <button class="more-button" onclick="showConfirmModal()">
        <img src="../result/images/shanchu.png" alt="">
   </button>
   
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
	   
    <div class="main" style="margin-top: 50px;">
        <div class="statistics order-details">
		    <p>总访客量：<span class="order-dingdan"><?php echo $totalVisitors;?></span></p>
            <p>昨日访客：<span class="order-dingdan"><?php echo $yesterdayVisitors;?></span></p>
            <p>今日访客：<span class="order-dingdan"><?php echo $todayVisitors;?></span></p>
		</div>
	<div class="visitor-list-grid">
	<?php
	if (empty($allVisitors)) {
	    echo '<p class="no-data">无数据</p>';
	} else {
	    foreach ($allVisitors as $visitor) {
	        ?>
	        <ul class="order-list">
	            <li class="order-item">
	                <div class="order-details">
	                    <p>地区: <span class="order-dingdan"><?php echo htmlspecialchars($visitor['ip_location']); ?></span></p>
	                    <p>设备: <span class="order-dingdan"><?php echo htmlspecialchars($visitor['user_agent']); ?></span></p>
	                    <p>时间: <span class="order-dingdan"><?php echo htmlspecialchars($visitor['visit_time']); ?></span></p>
	                    <p>页面: <span class="order-dingdan"><?php echo htmlspecialchars($visitor['page_url']); ?></span></p>
	                </div>
	            </li>
	        </ul>
	        <?php
	    }
	}
	?>
	</div>
	<!-- 分页导航 -->
	    <div class="pagination">
	        <?php if ($page > 1): ?>
	            <a href="?page=<?php echo $page - 1; ?>">上一页</a>
	        <?php endif; ?>
	        <?php if ($page < $totalPages): ?>
	            <a href="?page=<?php echo $page + 1; ?>">下一页</a>
	        <?php endif; ?>
	    </div>
</body>
<script>
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
            fetch("query-visitors.php", {
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

