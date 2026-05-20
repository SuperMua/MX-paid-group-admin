<?php
require_once 'login_check.php';
require_once 'users.php';
require_once 'query-visitors.php';
require_once 'dashboard_data.php';
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>付费进群系统</title>
    <link rel="stylesheet" href="../static/css/admin.css">
    <style>
       .scroll-container{width:220px;height:20px;overflow:hidden;white-space:nowrap;position:relative}
       .scroll-text{display:inline-block;font-size:14px;font-family:Arial,sans-serif;white-space:nowrap}
       @keyframes scroll-left{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}
       .dashboard-page{visibility:hidden}
       .dashboard-page.admin-shell-enabled,
       .dashboard-page.shell-ready{visibility:visible}
       .dashboard-layout{width:100%}
       .dashboard-aside{display:none}
       .dashboard-brand{display:flex;align-items:center;gap:10px;margin-bottom:18px}
       .dashboard-brand-badge{width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;background:linear-gradient(135deg,#f539f8,#5356fb)}
       .dashboard-brand-logo{width:36px;height:36px;border-radius:10px;object-fit:cover}
       .dashboard-brand-title{font-size:22px;font-weight:700;color:#2b3553;letter-spacing:.2px}
       .dashboard-section-title{margin:16px 0 10px;color:#5356fb;font-size:14px;font-weight:700}
       .sidebar-avatar{width:42px;height:42px;border-radius:50%;border:2px solid #fff;box-shadow:0 8px 16px rgba(83,86,251,.22)}
       .sidebar-user{display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:10px;border-radius:12px;background:#f8f7ff}
       .sidebar-user-meta{min-width:0}
       .sidebar-user-name{font-size:14px;font-weight:600;color:#2d3250;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
       .sidebar-user-tip{font-size:12px;color:#8b93b1;margin-top:2px}
       @media (min-width:992px){
           .dashboard-layout{display:grid;grid-template-columns:280px minmax(0,1fr);gap:20px;width:min(1400px,calc(100% - 28px));margin:12px auto}
           .dashboard-aside{display:flex;flex-direction:column;height:calc(100vh - 24px);position:sticky;top:12px;border:1px solid rgba(83,86,251,.15);border-radius:20px;padding:18px 14px;background:#fff;box-shadow:0 14px 32px rgba(83,86,251,.12);overflow:auto}
           .dashboard-aside .sidebar{display:flex;flex-direction:column;box-shadow:none;border:none;border-radius:14px;background:transparent}
           .dashboard-aside .menu-item{min-height:48px;border-radius:12px;border-bottom:none;margin-bottom:2px}
           .dashboard-aside .menu-item a{flex-direction:row;align-items:center;gap:2px;font-size:15px}
           .dashboard-aside .menu-arrow{margin-left:8px}
           .dashboard-aside .logout-button{width:100%;margin-top:auto}
           .dashboard-content{min-width:0}
           .body-ui{width:100%!important;margin-top:0}
           .header{text-align:left;padding:0 0 18px 10px}
           .dashboard-chip{display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.18);color:#fff;font-size:13px;border:1px solid rgba(255,255,255,.35);backdrop-filter:blur(4px)}
           .scroll-container{width:360px}
           .user-avatar{width:64px;height:64px}
           .main{display:none}
       }
    </style>
</head>
<body class="dashboard-page">
    <div class="dashboard-layout">
        <aside class="dashboard-aside">
            <div class="dashboard-brand" id="dashboardBrandWrap">
                <span class="dashboard-brand-badge" id="dashboardBrandBadge">NFT</span>
                <img src="" alt="品牌Logo" class="dashboard-brand-logo" id="dashboardBrandLogo" style="display:none">
                <span class="dashboard-brand-title" id="dashboardBrandTitle">运营后台</span>
            </div>
            <div class="sidebar-user">
                <img src="<?php echo htmlspecialchars($adminInfo['avatar']); ?>" alt="头像" class="sidebar-avatar">
                <div class="sidebar-user-meta">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($adminInfo['name']); ?></div>
                    <div class="sidebar-user-tip">欢迎回来，开始今日巡检</div>
                </div>
            </div>
            <div class="dashboard-section-title">功能菜单</div>
            <div class="sidebar">
                <div class="menu-item"><a href="index.php"><span class="menu-item-icon icon_1"></span>控制台</a><span class="menu-arrow"></span></div>
                <div class="menu-item"><a href="order.php"><span class="menu-item-icon icon_1"></span>查看订单</a><span class="menu-arrow"></span></div>
                <div class="menu-item"><a href="review_list.php"><span class="menu-item-icon icon_2"></span>任务审核</a><span class="menu-arrow"></span></div>
                <div class="menu-item"><a href="visitor.php"><span class="menu-item-icon icon_3"></span>访客记录</a><span class="menu-arrow"></span></div>
                <div class="menu-item"><a href="settings.php"><span class="menu-item-icon icon_4"></span>系统设置</a><span class="menu-arrow"></span></div>
                <div class="menu-item"><a href="upload_cache.php"><span class="menu-item-icon icon_5"></span>缓存清理</a><span class="menu-arrow"></span></div>
                <div class="menu-item"><a href="virtual_data.php"><span class="menu-item-icon icon_5"></span>虚拟数据</a><span class="menu-arrow"></span></div>
            </div>
            <form action="logout.php" method="post"><button class="logout-button">退出登录</button></form>
        </aside>

        <div class="dashboard-content">
            <div class="body-ui">
                <div class="header"><span class="dashboard-chip">运营控制台</span></div>
                <div class="user-info">
                    <img src="<?php echo htmlspecialchars($adminInfo['avatar']); ?>" alt="头像" class="user-avatar">
                    <div class="user-details">
                        <span>昵称：<?php echo htmlspecialchars($adminInfo['name']); ?></span><br>
                        <div class="scroll-container"><div class="scroll-text" id="scrollText"><?php echo $greeting; ?>! <?php echo $warmWords; ?></div></div>
                    </div>
                </div>

                <!-- 8 metric cards -->
                <div class="dash-cards-grid">
                    <div class="dash-card"><div class="dash-card-icon c1">💰</div><div><div class="dash-card-val">¥<?php echo $dashTotalIncomeFmt; ?></div><div class="dash-card-lbl">累计总收入</div></div></div>
                    <div class="dash-card"><div class="dash-card-icon c2">📈</div><div><div class="dash-card-val">¥<?php echo $dashTodayIncomeFmt; ?></div><div class="dash-card-lbl">今日收入</div></div></div>
                    <div class="dash-card"><div class="dash-card-icon c3">📦</div><div><div class="dash-card-val"><?php echo $dashTotalOrders; ?></div><div class="dash-card-lbl">总订单数</div></div></div>
                    <div class="dash-card"><div class="dash-card-icon c4">👁️</div><div><div class="dash-card-val"><?php echo $dashTotalVisitors; ?></div><div class="dash-card-lbl">累计访客</div></div></div>
                    <div class="dash-card"><div class="dash-card-icon c5">🎯</div><div><div class="dash-card-val"><?php echo $dashConversion; ?>%</div><div class="dash-card-lbl">支付转化率</div></div></div>
                    <div class="dash-card"><div class="dash-card-icon c6">💳</div><div><div class="dash-card-val">¥<?php echo $dashAvgOrderFmt; ?></div><div class="dash-card-lbl">客单价</div></div></div>
                    <div class="dash-card"><div class="dash-card-icon c7">⚠️</div><div><div class="dash-card-val"><?php echo $dashUnpaidCount; ?></div><div class="dash-card-lbl">未支付订单</div></div></div>
                    <div class="dash-card"><div class="dash-card-icon c8">🕐</div><div><div class="dash-card-val"><?php echo $dashTodayOrders; ?></div><div class="dash-card-lbl">今日订单数</div></div></div>
                </div>

                <!-- Charts row -->
                <div class="dash-chart-row">
                    <div class="dash-chart-panel">
                        <h4>近7天收入与访客趋势 <span class="tag">实时</span></h4>
                        <div style="height:240px"><canvas id="chartTrend"></canvas></div>
                    </div>
                    <div class="dash-chart-panel">
                        <h4>支付方式分布</h4>
                        <div style="height:220px"><canvas id="chartPaymentMethod"></canvas></div>
                        <div id="noPayData" style="display:none;text-align:center;padding:24px;color:#94a3b8;font-size:13px">暂无支付数据</div>
                    </div>
                </div>

                <!-- Recent orders + locations -->
                <div class="dash-bottom-row">
                    <div class="dash-chart-panel">
                        <h4>最近订单 <span class="tag">最新5笔</span></h4>
                        <?php if (empty($dashRecentOrders)): ?>
                            <div class="dash-empty">暂无订单数据</div>
                        <?php else: ?>
                            <?php foreach ($dashRecentOrders as $ro): ?>
                            <div class="dash-order-mini">
                                <div><div class="n"><?php echo htmlspecialchars($ro['name']); ?></div><div class="m"><?php echo ($ro['payment_method'] ?? '') === 'wxpay' ? '微信' : (($ro['payment_method'] ?? '') === 'alipay' ? '支付宝' : '未知'); ?> · <?php echo htmlspecialchars($ro['payment_time'] ?? ''); ?></div></div>
                                <div><div class="a <?php echo ($ro['payment_status'] ?? '') === '已支付' ? 'paid' : 'unpaid'; ?>">¥<?php echo htmlspecialchars($ro['money'] ?? '0.00'); ?></div><div class="m" style="text-align:right"><?php echo htmlspecialchars($ro['payment_status'] ?? ''); ?></div></div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="dash-chart-panel">
                        <h4>地区分布 TOP5</h4>
                        <?php if (empty($dashTopLocs)): ?>
                            <div class="dash-empty">暂无数据</div>
                        <?php else: ?>
                            <?php $locMax = reset($dashTopLocs); ?>
                            <?php foreach ($dashTopLocs as $locName => $locCount): ?>
                            <div class="dash-loc-row">
                                <span class="dash-loc-name"><?php echo htmlspecialchars($locName); ?></span>
                                <div class="dash-loc-bar-bg"><div class="dash-loc-bar-fg" style="width:<?php echo $locMax > 0 ? round($locCount / $locMax * 100) : 0; ?>%"></div></div>
                                <span class="dash-loc-val"><?php echo $locCount; ?>单</span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Second chart row -->
                <div class="dash-chart-row equal">
                    <div class="dash-chart-panel">
                        <h4>支付状态占比</h4>
                        <div style="height:200px"><canvas id="chartPayStatus"></canvas></div>
                    </div>
                    <div class="dash-chart-panel">
                        <h4>今日时段分布</h4>
                        <div style="height:200px"><canvas id="chartHourly"></canvas></div>
                    </div>
                </div>

            </div><!-- /body-ui -->

            <div class="main" style="display:none">
                <div class="sidebar">
                    <div class="menu-item"><a href="index.php"><span class="menu-item-icon icon_1"></span>控制台</a><span class="menu-arrow"></span></div>
                    <div class="menu-item"><a href="order.php"><span class="menu-item-icon icon_1"></span>查看订单</a><span class="menu-arrow"></span></div>
                    <div class="menu-item"><a href="review_list.php"><span class="menu-item-icon icon_2"></span>任务审核</a><span class="menu-arrow"></span></div>
                    <div class="menu-item"><a href="visitor.php"><span class="menu-item-icon icon_3"></span>访客记录</a><span class="menu-arrow"></span></div>
                    <div class="menu-item"><a href="settings.php"><span class="menu-item-icon icon_4"></span>系统设置</a><span class="menu-arrow"></span></div>
                    <div class="menu-item"><a href="upload_cache.php"><span class="menu-item-icon icon_5"></span>缓存清理</a><span class="menu-arrow"></span></div>
                    <div class="menu-item"><a href="virtual_data.php"><span class="menu-item-icon icon_5"></span>虚拟数据</a><span class="menu-arrow"></span></div>
                </div>
                <form action="logout.php" method="post"><button class="logout-button">退出登录</button></form>
            </div>
        </div>
    </div>

<script src="https://cdn.bootcdn.net/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function(){
var charts={};
function dk(k){if(charts[k]){charts[k].destroy();delete charts[k]}}

// Data from PHP
var dashData = {
    trend: <?php echo json_encode($dashTrend, JSON_UNESCAPED_UNICODE); ?>,
    payment_methods: [
        {name:'微信支付',value:<?php echo $wx; ?>},
        {name:'支付宝',value:<?php echo $ali; ?>}
    ],
    payment_status: [
        {name:'已支付',value:<?php echo $dashPaidCount; ?>},
        {name:'未支付',value:<?php echo $dashUnpaidCount; ?>}
    ],
    hourly: <?php echo json_encode($dashHourly); ?>
};

function chartTrend(d){
    dk('trend');var c=document.getElementById('chartTrend');if(!c)return;
    charts.trend=new Chart(c,{type:'line',data:{labels:d.trend.map(function(t){return t.date}),datasets:[
        {label:'收入 (¥)',data:d.trend.map(function(t){return t.income}),borderColor:'#5356fb',backgroundColor:'rgba(83,86,251,0.06)',fill:true,tension:0.4,yAxisID:'y',pointRadius:4,pointBackgroundColor:'#5356fb'},
        {label:'访客',data:d.trend.map(function(t){return t.visitors}),borderColor:'#f539f8',backgroundColor:'rgba(245,57,248,0.04)',fill:true,tension:0.4,yAxisID:'y1',pointRadius:4,pointBackgroundColor:'#f539f8'}
    ]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:20}}},scales:{y:{type:'linear',position:'left',title:{display:true,text:'收入'},grid:{color:'#f1f5f9'}},y1:{type:'linear',position:'right',title:{display:true,text:'访客'},grid:{display:false}},x:{grid:{display:false}}}});
}
function chartPie(d,key,canvasId,colors){
    dk(key);var c=document.getElementById(canvasId);if(!c)return;
    var labels=key==='payMethod'?d.payment_methods.map(function(p){return p.name}):d.payment_status.map(function(p){return p.name});
    var vals=key==='payMethod'?d.payment_methods.map(function(p){return p.value}):d.payment_status.map(function(p){return p.value});
    var total=vals.reduce(function(a,b){return a+b},0);
    if(total===0){c.parentElement.style.display='none';return}
    c.parentElement.style.display='';
    charts[key]=new Chart(c,{type:'doughnut',data:{labels:labels,datasets:[{data:vals,backgroundColor:colors,borderWidth:0,borderRadius:3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:16}}}}});
}
function chartHourly(d){
    dk('hourly');var c=document.getElementById('chartHourly');if(!c)return;
    var labels=[];for(var i=0;i<24;i++)labels.push(i+':00');
    charts.hourly=new Chart(c,{type:'bar',data:{labels:labels,datasets:[{label:'订单',data:d.hourly,backgroundColor:'#5356fb',borderRadius:3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'},ticks:{stepSize:1}},x:{grid:{display:false},ticks:{maxTicksLimit:8}}}});
}

function initCharts(){
    chartTrend(dashData);
    chartPie(dashData,'payMethod','chartPaymentMethod',['#5356fb','#f539f8']);
    chartPie(dashData,'payStatus','chartPayStatus',['#10b981','#f59e0b']);
    chartHourly(dashData);
}

// Double rAF guarantees layout is complete before Chart.js reads canvas dimensions.
function _chartsSafe(fn){
    requestAnimationFrame(function(){
        requestAnimationFrame(function(){
            if(typeof Chart==='undefined')return;
            fn();
        });
    });
}
function _bootCharts(){
    document.body.classList.add('shell-ready');
    _chartsSafe(initCharts);
}
// Delay chart init to let admin-shell finish DOM move (desktop) or just layout settle (mobile).
// The body is visibility:hidden until admin-shell-enabled (added by admin-shell.js) or shell-ready
// (added here) becomes visible — eliminating the sidebar flash.
setTimeout(_bootCharts,250);
// Re-render charts after SPA page navigation
window.addEventListener('admin-shell:page-loaded',function(){
    setTimeout(_bootCharts,100);
});
})();

function applyBrandSettings(){
    fetch('brand_settings_api.php').then(function(r){return r.json()}).then(function(p){
        if(!p||!p.success)return;var d=p.data||{},bn=(d.brand_name||'').trim(),lp=(d.logo_path||'').trim(),fp=(d.favicon_path||'').trim();
        if(bn){var t=document.getElementById('dashboardBrandTitle');if(t)t.textContent=bn;document.title=bn+' - 控制台'}
        if(lp){var l=document.getElementById('dashboardBrandLogo'),b=document.getElementById('dashboardBrandBadge');if(l){l.src=lp;l.style.display='inline-block'}if(b)b.style.display='none'}
        if(fp){var f=document.querySelector('link[rel="icon"]');if(!f){f=document.createElement('link');f.rel='icon';document.head.appendChild(f)}f.href=fp+(fp.indexOf('?')===-1?'?v=':'&v=')+Date.now()}
    }).catch(function(){})
}
function initDashboardPage(){
    var r=document.querySelector('.dashboard-content')||document.body;
    if(!r||r.dataset.boundDashboardPage==='1')return;r.dataset.boundDashboardPage='1';
    applyBrandSettings();startScroll()
}
function startScroll(){
    var t=document.getElementById('scrollText'),c=document.querySelector('.scroll-container');if(!t||!c)return;
    if(t.scrollWidth>c.offsetWidth){setTimeout(function(){t.style.animation='scroll-left 10s linear forwards';t.addEventListener('animationend',function(){t.style.transform='translateX(0)';t.style.animation='none';setTimeout(function(){startScroll()},2000)})},2000)}
}
initDashboardPage();
</script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>
