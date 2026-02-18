<?php
require_once 'login_check.php';  //验证是否登陆
require_once 'users.php';   //加载数据
require_once 'query-visitors.php'; //加载数据
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>付费进群系统</title>
	<link rel="stylesheet" href="../static/css/admin.css">
    <style>
       /* 容器样式 */
       .scroll-container {
           width: 220px; /* 容器宽度 */
           height: 20px; /* 容器高度 */
           overflow: hidden; /* 隐藏超出部分 */
           white-space: nowrap; /* 文本不换行 */
           position: relative; /* 设置相对定位 */
       }
       
       /* 文本样式 */
       .scroll-text {
           display: inline-block; /* 设置为行内块元素 */
           font-size: 14px; /* 字体大小 */
           font-family: Arial, sans-serif; /* 字体样式 */
           white-space: nowrap; /* 文本不换行 */
       }
	   /* 定义滚动动画 */
       @keyframes scroll-left {
	       0% {
	           transform: translateX(0); /* 从容器左侧开始 */
	       }
	       100% {
	           transform: translateX(-100%); /* 滚动到容器宽度的负值 */
	       }
	   }

	   .dashboard-chip {
		   display: none;
	   }

	   @media (min-width: 992px) {
		   .body-ui {
			   background: linear-gradient(135deg, #1677ff 0%, #66a8ff 28%, #eef2f7 28%);
		   }

		   .header {
			   text-align: left;
			   padding: 0 0 18px 10px;
		   }

		   .dashboard-chip {
			   display: inline-flex;
			   align-items: center;
			   padding: 6px 12px;
			   border-radius: 999px;
			   background: rgba(255, 255, 255, 0.2);
			   color: #fff;
			   font-size: 13px;
			   border: 1px solid rgba(255, 255, 255, 0.25);
			   backdrop-filter: blur(4px);
		   }

		   .scroll-container {
			   width: 360px;
		   }

		   .user-avatar {
			   width: 64px;
			   height: 64px;
		   }
	   }
    </style>
</head>
<body>
	<div class="body-ui">
		<div class="header">
		     <span class="dashboard-chip">运营控制台</span>
        </div>
		<div class="user-info">
		    <img src="<?php echo htmlspecialchars($adminInfo['avatar']); ?>" alt="用户头像" class="user-avatar">
		    <div class="user-details">
			    <span>昵称：<?php echo htmlspecialchars($adminInfo['name']); ?></span><br>
				<div class="scroll-container">
				    <div class="scroll-text" id="scrollText">
				         <?php echo $greeting; ?>! <?php echo $warmWords; ?>
				    </div>
				</div>
		        <!--<span>日期：<?php echo htmlspecialchars($adminInfo['date']); ?></span>-->
		    </div>
		</div>
		<div class="stats">
		    <div class="stat-item">
		        <p class="stat-value"><?php echo number_format($totalIncome, 2); ?></p>
		        <p>总收入</p>
		    </div>
		    <div class="stat-item">
		        <p class="stat-value"><?php echo number_format($todayIncome, 2); ?></p>
		        <p>今日收入</p>
		    </div>
		    <div class="stat-item">
		        <p class="stat-value"><?php echo $todayOrders; ?></p>
		        <p>今日订单</p>
		    </div>
		</div>
		
		<div class="stats">
		    <div class="stat-item">
			    <a href="visitor.php">
		        <p class="stat-value"><?php echo $yesterdayVisitors; ?></p>
		        <p>昨日访客</p>
				</a>
		    </div>
		    <div class="stat-item">
			    <a href="visitor.php">
		        <p class="stat-value"><?php echo $todayVisitors; ?></p>
		        <p>今日访客</p>
				</a>
		    </div>
		    <div class="stat-item">
			    <a href="review_list.php">
		        <p class="stat-value"><?php echo $unreviewedCount; ?></p>
		        <p>待审核</p>
				</a>
		    </div>
		</div>
	</div>
	<div style="margin-top: 10px;"></div>
	<div class="main">
		    <div class="sidebar">
		        <div class="menu-item">
		            <a href="order.php">
		                <span class="menu-item-icon icon_1"></span>查看订单
					</a>
		            <span class="menu-arrow"></span>
		        </div>
				<div class="menu-item">
				    <a href="review_list.php">
				        <span class="menu-item-icon icon_2"></span>任务审核
					</a>
				    <span class="menu-arrow"></span>
				</div>
				<div class="menu-item">
				    <a href="visitor.php">
				        <span class="menu-item-icon icon_3"></span>访客记录
					</a>
				    <span class="menu-arrow"></span>
				</div>
				<div class="menu-item">
				    <a href="settings.php">
				        <span class="menu-item-icon icon_4"></span>系统设置
					</a>
				    <span class="menu-arrow"></span>
				</div>
				<div class="menu-item">
				    <a href="upload_cache.php">
				        <span class="menu-item-icon icon_5"></span>其他
					</a>
				    <span class="menu-arrow"></span>
				</div>
		    </div>
			<form action="logout.php" method="post">
			    <button class="logout-button">退出登录</button>
			</form>
	      </div>
</body>
<script>
    // 页面加载完成后延迟2秒开始滚动
    window.onload = function() {
        startScroll();
    };

    function startScroll() {
        var scrollText = document.getElementById('scrollText');
        var container = document.querySelector('.scroll-container');
        var scrollWidth = scrollText.scrollWidth; // 获取文本的总宽度
        var containerWidth = container.offsetWidth; // 获取容器的宽度

        // 如果文本宽度大于容器宽度，则开始滚动
        if (scrollWidth > containerWidth) {
            // 动画延迟2秒开始
            setTimeout(function() {
                scrollText.style.animation = 'scroll-left 10s linear forwards'; // 设置动画
                scrollText.addEventListener('animationend', function() {
                    // 动画结束后重置文本位置
                    scrollText.style.transform = 'translateX(0)';
                    scrollText.style.animation = 'none'; // 移除动画
                    // 延迟2秒再次滚动
                    setTimeout(function() {
                        startScroll(); // 递归调用，重新开始滚动
                    }, 2000); // 延迟2秒
                });
            }, 2000); // 初始延迟2秒
        }
    }
</script>

</html>
