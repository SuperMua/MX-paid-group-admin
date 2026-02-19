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

	   .dashboard-layout {
		   width: 100%;
	   }

	   .dashboard-aside {
		   display: none;
	   }

	   .dashboard-brand {
		   display: flex;
		   align-items: center;
		   gap: 10px;
		   margin-bottom: 18px;
	   }

	   .dashboard-brand-badge {
		   width: 36px;
		   height: 36px;
		   border-radius: 50%;
		   display: inline-flex;
		   align-items: center;
		   justify-content: center;
		   font-size: 13px;
		   font-weight: 700;
		   color: #fff;
		   background: linear-gradient(135deg, #f539f8, #5356fb);
	   }

	   .dashboard-brand-logo {
		   width: 36px;
		   height: 36px;
		   border-radius: 10px;
		   object-fit: cover;
	   }

	   .dashboard-brand-title {
		   font-size: 22px;
		   font-weight: 700;
		   color: #2b3553;
		   letter-spacing: 0.2px;
	   }

	   .dashboard-section-title {
		   margin: 16px 0 10px;
		   color: #5356fb;
		   font-size: 14px;
		   font-weight: 700;
	   }

	   .sidebar-avatar {
		   width: 42px;
		   height: 42px;
		   border-radius: 50%;
		   border: 2px solid #fff;
		   box-shadow: 0 8px 16px rgba(83, 86, 251, 0.22);
	   }

	   .sidebar-user {
		   display: flex;
		   align-items: center;
		   gap: 10px;
		   margin-bottom: 12px;
		   padding: 10px;
		   border-radius: 12px;
		   background: #f8f7ff;
	   }

	   .sidebar-user-meta {
		   min-width: 0;
	   }

	   .sidebar-user-name {
		   font-size: 14px;
		   font-weight: 600;
		   color: #2d3250;
		   white-space: nowrap;
		   overflow: hidden;
		   text-overflow: ellipsis;
	   }

	   .sidebar-user-tip {
		   font-size: 12px;
		   color: #8b93b1;
		   margin-top: 2px;
	   }

	   @media (min-width: 992px) {
		   .dashboard-layout {
			   display: grid;
			   grid-template-columns: 280px minmax(0, 1fr);
			   gap: 20px;
			   width: min(1400px, calc(100% - 28px));
			   margin: 12px auto;
		   }

		   .dashboard-aside {
			   display: flex;
			   flex-direction: column;
			   height: calc(100vh - 24px);
			   position: sticky;
			   top: 12px;
			   border: 1px solid rgba(83, 86, 251, 0.15);
			   border-radius: 20px;
			   padding: 18px 14px;
			   background: #fff;
			   box-shadow: 0 14px 32px rgba(83, 86, 251, 0.12);
			   overflow: auto;
		   }

		   .dashboard-aside .sidebar {
			   display: flex;
			   flex-direction: column;
			   box-shadow: none;
			   border: none;
			   border-radius: 14px;
			   background: transparent;
		   }

		   .dashboard-aside .menu-item {
			   min-height: 48px;
			   border-radius: 12px;
			   border-bottom: none;
			   margin-bottom: 2px;
		   }

		   .dashboard-aside .menu-item a {
			   flex-direction: row;
			   align-items: center;
			   gap: 2px;
			   font-size: 15px;
		   }

		   .dashboard-aside .menu-arrow {
			   margin-left: 8px;
		   }

		   .dashboard-aside .logout-button {
			   width: 100%;
			   margin-top: auto;
		   }

		   .dashboard-content {
			   min-width: 0;
		   }

		   .body-ui {
			   width: 100% !important;
			   margin-top: 0;
			   background: linear-gradient(134.38deg, #f539f8 0%, #c342f9 43.55%, #5356fb 104.51%);
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
			   background: rgba(255, 255, 255, 0.18);
			   color: #fff;
			   font-size: 13px;
			   border: 1px solid rgba(255, 255, 255, 0.35);
			   backdrop-filter: blur(4px);
		   }

		   .scroll-container {
			   width: 360px;
		   }

		   .user-avatar {
			   width: 64px;
			   height: 64px;
		   }

		   .main {
			   display: none;
		   }
	   }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="dashboard-aside">
            <div class="dashboard-brand" id="dashboardBrandWrap">
                <span class="dashboard-brand-badge" id="dashboardBrandBadge">NFT</span>
                <img src="" alt="品牌Logo" class="dashboard-brand-logo" id="dashboardBrandLogo" style="display:none;">
                <span class="dashboard-brand-title" id="dashboardBrandTitle">运营后台</span>
            </div>
            <div class="sidebar-user">
                <img src="<?php echo htmlspecialchars($adminInfo['avatar']); ?>" alt="用户头像" class="sidebar-avatar">
                <div class="sidebar-user-meta">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($adminInfo['name']); ?></div>
                    <div class="sidebar-user-tip">欢迎回来，开始今日巡检</div>
                </div>
            </div>
            <div class="dashboard-section-title">功能菜单</div>
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
        </aside>

        <div class="dashboard-content">
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
        </div>
    </div>
<script>
    function applyBrandSettings() {
        fetch('brand_settings_api.php')
            .then(function(response) {
                return response.json();
            })
            .then(function(payload) {
                if (!payload || !payload.success) {
                    return;
                }
                var data = payload.data || {};
                var brandName = (data.brand_name || '').trim();
                var logoPath = (data.logo_path || '').trim();
                var faviconPath = (data.favicon_path || '').trim();

                if (brandName) {
                    var titleNode = document.getElementById('dashboardBrandTitle');
                    if (titleNode) {
                        titleNode.textContent = brandName;
                    }
                    document.title = brandName + ' - 控制台';
                }

                if (logoPath) {
                    var logoNode = document.getElementById('dashboardBrandLogo');
                    var badgeNode = document.getElementById('dashboardBrandBadge');
                    if (logoNode) {
                        logoNode.src = logoPath;
                        logoNode.style.display = 'inline-block';
                    }
                    if (badgeNode) {
                        badgeNode.style.display = 'none';
                    }
                }

                if (faviconPath) {
                    var faviconNode = document.querySelector('link[rel="icon"]');
                    if (!faviconNode) {
                        faviconNode = document.createElement('link');
                        faviconNode.rel = 'icon';
                        document.head.appendChild(faviconNode);
                    }
                    faviconNode.href = faviconPath + (faviconPath.indexOf('?') === -1 ? '?v=' : '&v=') + Date.now();
                }
            })
            .catch(function() {
                // 忽略品牌配置读取失败，保留默认展示
            });
    }

    function initDashboardPage() {
        var dashboardRoot = document.querySelector('.dashboard-content') || document.body;
        if (!dashboardRoot || dashboardRoot.dataset.boundDashboardPage === '1') {
            return;
        }
        dashboardRoot.dataset.boundDashboardPage = '1';
        applyBrandSettings();
        startScroll();
    }

    function startScroll() {
        var scrollText = document.getElementById('scrollText');
        var container = document.querySelector('.scroll-container');
        if (!scrollText || !container) {
            return;
        }
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

    initDashboardPage();
</script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>


