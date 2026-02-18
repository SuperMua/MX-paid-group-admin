<?php
require_once 'check.php';//验证支付状态
require_once 'settings_data.php';
require '../config/config.php';
require_once 'image_upload.php';

// 初始化数据容器
$data = [
    'groups' => [],
    'temp_data' => []
];

// 查询template表数据
$sql1 = "SELECT nickname, content, group_avatar FROM template ORDER BY RAND()";
if ($result = $conn->query($sql1)) {
    while($row = $result->fetch_assoc()) {
        $data['groups'][] = $row;
    }
    $result->free(); // 释放结果集
} else {
    die("template表查询失败: " . $conn->error);
}

// 查询temp表数据
$sql2 = "SELECT amount, text_field FROM temp";
if ($result = $conn->query($sql2)) {
    while($row = $result->fetch_assoc()) {
        $data['temp_data'][] = $row;
    }
    $result->free(); // 释放结果集
} else {
    die("temp表查询失败: " . $conn->error);
}

// 关闭连接
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/style2.css">
    <title>加入<?php echo $city;?>群聊</title>
    <style>
       
    </style>
</head>
<body>
    <div class="container">
        <div class="group-list">
            <!-- 群组列表 -->	 
            <?php if (!empty($data['groups'])): ?>
            <?php foreach ($data['groups'] as $group): ?>
                <div class="group-item">
                    <img src="<?php echo !empty($group['group_avatar']) ? htmlspecialchars($group['group_avatar']) : 'images/default-avatar.jpg'; ?>" 
                         class="avatar" alt="群头像"onerror="this.src='images/default-avatar.jpg'">
                    <div class="group-info">
                        <div class="group-title">
                            <span class="group-name"><?php echo $city; ?><?php echo htmlspecialchars($group['nickname']); ?></span>
                            <span class="group-time"></span>
                        </div>
                        <div class="group-desc">
                            <?php echo array_shift($nicknames); ?>：<?php echo htmlspecialchars($group['content']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-groups">暂无数据</div>
        <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($data['temp_data']) && isset($data['temp_data'][0])): ?>
        <?php $temp = $data['temp_data'][0]; ?>
        <a href="#" class="join-btn" id="openPopup"><?php echo htmlspecialchars($temp["text_field"] ?? '加入群聊'); ?></a>

        <!-- 弹窗 -->
        <div class="popup" id="popup">
            <div class="popup-content">
                <div class="popup-header">
                    <span class="popup-title">限时进群</span>
                </div>
                <div class="popup-body">
                    <p>打赏<span class="popup-yuan"><?php echo isset($temp["amount"]) ? htmlspecialchars(number_format($temp["amount"], 1)) : '0.0'; ?></span>元进群</p>
                    <p>优惠还剩最后：<span id="timer">00:00:20</span></p>
                </div>
                <div class="popup-footer">
                    <a href="payds.php" class="confirm-btn">确认</a>
                    <a href="upload.php" class="confirm-text">不想打赏？做任务免费进群></a>
                    <button class="close-btn" id="closePopup">×</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- 任务进度弹窗 -->
	 <?php if ($hasUpload) {?>
	       <div id="myModal" class="modal-k">
	           <div class="modal-content-k">
	               <span class="close">&times;</span>
	               <div class="modal-message">您已提交审核</div>
	               <button class="buttonlk" onclick="window.location.href='success_page.php'">查看进度</button>
	           </div>
	       </div>
	   <?php }?>
	   
    <script>
        // 动态生成时间
        function generateRecentTime() {
            const now = new Date();
            const randomMinutes = Math.floor(Math.random() * 30); // 0-30分钟随机
            const newTime = new Date(now.getTime() - randomMinutes * 60000);
            
            const hours = newTime.getHours().toString().padStart(2, '0');
            const minutes = newTime.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        }

        // 更新所有时间显示
        document.querySelectorAll('.group-time').forEach(timeElement => {
            timeElement.textContent = generateRecentTime();
        });
		
		//弹窗
		document.getElementById('openPopup').addEventListener('click', function(event) {
		    event.preventDefault();
		    document.getElementById('popup').style.display = 'flex';
		});
		
		document.getElementById('closePopup').addEventListener('click', function() {
		    document.getElementById('popup').style.display = 'none';
		});
		
		//倒计时
		// 获取倒计时的元素
		const timerElement = document.getElementById('timer');
		
		// 设置倒计时时间（24分钟）
		let timeLeft = 24 * 60; // 24分钟转换为秒
		
		// 更新倒计时的函数
		function updateTimer() {
		    // 计算分钟和秒数
		    const minutes = Math.floor(timeLeft / 60);
		    const seconds = timeLeft % 60;
		
		    // 格式化时间显示为“00:分:秒”格式
		    const formattedTime = `00:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
		
		    // 更新页面上的时间显示
		    timerElement.textContent = formattedTime;
		
		    // 如果时间结束，重置倒计时
		    if (timeLeft <= 0) {
		        timeLeft = 24 * 60; // 重置为24分钟
		    }
		
		    // 时间减1
		    timeLeft--;
		}
		
		// 每秒更新一次倒计时
		setInterval(updateTimer, 1000);
		
		// 立即更新一次倒计时，避免等待第一秒
		updateTimer();
    </script>
    <script src="../static/js/upload.js"></script>
	<script src="../static/js/visitor.js"></script>
</body>
</html>

