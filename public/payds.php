<?php
require_once 'check.php';//验证支付状态
require_once 'settings_data.php';
require __DIR__ . '/../config/config.php';
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
    <title>确认打赏</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
           
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
		a {
			text-decoration: none;
			/* 去除下划线 */
			color: inherit;
			/* 继承父元素的颜色 */
			-webkit-tap-highlight-color: transparent;
			-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
		}
        .navbar {
        		position: fixed;
        		top: 0;
        		left: 0;
        		width: 100%;
        		height: 45px;
        		/* 导航栏高度 */
        		background-color: #fff;
        		/* 背景色 */
        		z-index: 9999;
        		/* 确保导航栏在最上层 */
        		display: flex;
        		align-items: center;
        		border-bottom: 0.06rem solid #ebebeb;
        	}
        	.back-button {
        		width: 20px;
        		height: 20px;
        		z-index: 99999;
        		margin-left: 10px;
        		position: fixed;
        	}
        	.left-arrow {
        		display: inline-block;
        		margin: 20px;
        		border-left: 1px solid;
        		border-bottom: 1px solid;
        		width: 10px;
        		height: 10px;
        		transform: rotate(45deg);
        	}
        	.title {
        		margin: auto;
        		z-index: 999;
        	}
        .container {
            width: 95%;
            max-width: 400px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        /* 金额显示 */
        .amount-box {
            text-align: center;
            margin: 30px 0;
        }
        .amount-label {
            color:#8f8d8d;
            font-size: 15px;
        }
        .amount-number {
            font-size: 28px;
            font-weight: bold;
            color: #ff2121;
        }

        .vehicle-info {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
			margin-top: 30px;
        }
        .vehicle-lep{
            font-size: 16px;
			font-weight: bold;
            color: #363636;
            margin-bottom: 6px;
        }
        .vehicle-cp{
            font-size: 15px;
            color: #8f8d8d;
			margin-bottom: 10px;
        }
        .payment-methods{
			margin-top: 20px;
		}
        /* 支付方式 */
        .payment-title {
            font-size: 16px;
            color: #8f8d8d;
            padding: 15px 0 10px;
            text-align: left;
        }
        .payment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0;
            border-bottom: 1px solid #eee;
        }
        .payment-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .payment-icon {
            width: 30px;
            height: 30px;
        }
        .payment-name {
            font-size: 16px;
            color: #333;
        }
        input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #328cff;
        }

        /* 支付按钮 */
        .pay-button {
            width: 100%;
            background: #07C160;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 17px;
            margin-top: 50px;
            cursor: pointer;
            transition: opacity 0.2s;
			margin-bottom: 30px;
        }
        .pay-button:active {
            opacity: 0.8;
        }
         input{
			border: none; /* 移除默认的边框 */
			outline: none;
		 }
    </style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="#" onclick="history.length>1 ? history.back() : location.href=document.referrer||'/';"></a>
        <div class="title">确认打赏</div>
    </div>
    <div class="container">
	 <form action="db.php" method="post">
        <!-- 金额显示 -->
		<?php if (!empty($data['temp_data']) && isset($data['temp_data'][0])): ?>
		    <?php $temp = $data['temp_data'][0]; ?>
        <div class="amount-box">
            <div class="amount-label">打赏金额</div>
            <div class="amount-number"><?php echo isset($temp["amount"]) ? htmlspecialchars(number_format($temp["amount"], 1)) : '0.0'; ?>元</div>
        </div>

        <div class="vehicle-info">
		    <div class="vehicle-cp">订单类型：<span class="vehicle-lep"><input type="" id="name" name="name" value="付费订单" readonly></sapn></div>
            <div class="vehicle-cp">订单编号：<span class="vehicle-lep"><input type="" name="WIDout_trade_no" value="<?php echo date("YmdHis").mt_rand(100,999); ?>"/></sapn></div>
			<div class="vehicle-cp">创建时间：<span class="vehicle-lep"><input type="" name="payment_time" value="<?php echo $currentTime; ?>" readonly></sapn></div>
        </div>
		<input type="hidden" id="money" name="money" value="<?php echo isset($temp["amount"]) ? htmlspecialchars(number_format($temp["amount"], 1)) : '0.0'; ?>" readonly>
        <input type="hidden" size="30" name="WIDsubject" value="自营商品"/>
        <input type="hidden" id="name" name="name" value="付费订单" readonly>
        <input type="hidden" id="ip_address" name="ip_address" value="<?php echo $userIP; ?>" readonly>
        <input type="hidden" id="ip_location" name="ip_location" value="<?php echo $ipLocation; ?>" readonly>
        <!-- 支付方式 -->
        <div class="payment-methods">
            <h4 class="payment-title">支付方式</h4>
            <label class="payment-item">
                <div class="payment-left">
                    <img src="../result/images/wxzf.png" class="payment-icon" alt="微信">
                    <span class="payment-name">微信支付</span>
                </div>
                <input type="radio" name="payment_method" value="wxpay" checked="">
            </label>

            <label class="payment-item">
                <div class="payment-left">
                    <img src="../result/images/zfbzf.png" class="payment-icon" alt="支付宝">
                    <span class="payment-name">支付宝支付</span>
                </div>
                <input type="radio" name="payment_method" value="alipay">
            </label>
        </div>

        <button class="pay-button">立即支付</button>
		<?php endif; ?>
	 </form>
    </div>
</body>
</html>