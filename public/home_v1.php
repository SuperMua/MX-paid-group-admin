<?php
require_once 'check.php';
require_once 'settings_data.php';
require_once 'image_upload.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>加入<?php echo $city;?>群聊</title>
	<link rel="stylesheet" href="../static/css/style.css">
	<style>
	   .container {
	       max-width: 600px;
	       margin: 0 auto;
	   }
	</style>
</head>  
<body>
<div class="container">
<!-- 信息容器 -->
    <div class="info-container" id="infoContainer"></div>
    <!-- 弹窗内容 -->
    <!-- <div class="masktx"></div>
    <div class="popuptx">
        <span class="close-btn" onclick="closePopup1()">×</span>
        <h3>温馨提示</h3>
        <ul>
            <li><?php echo $warm_tip ?></li>
        </ul>
        <a href="upload.php"><button class="popup-btn">查看任务</button></a>
        <button class="popup-btn" onclick="closePopup1()">知道了</button>
    </div> -->
    
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
	   
	   
	<div class="price" onclick="openPopup()"><?php echo $ordering ?></div>
	<div class="overlay" id="overlay"></div>
	    <div class="popup" id="popup">
	        <div class="popup-header">
	           <b>打赏进群</b><span class="close-btn" onclick="closePopup()">×</span>
	        </div>
			<form action="db.php" method="post">
				    <input type="hidden" size="30" name="WIDout_trade_no" value="<?php echo date("YmdHis").mt_rand(100,999); ?>"/>
				    <input type="hidden" size="30" name="WIDsubject" value="自营商品"/>
					<input type="hidden" id="name" name="name" value="付费订单" readonly>
					<input type="hidden" id="ip_address" name="ip_address" value="<?php echo $userIP; ?>" readonly>
					<input type="hidden" id="ip_location" name="ip_location" value="<?php echo $ipLocation; ?>" readonly>
	                <div class="dsyuan">原价<del><?php echo $original_price; ?>元</del> 限时<input type="number" id="money" name="money" value="<?php echo $entry_price; ?>" readonly>元进群</div>
	                <!--倒计时-->
					<div class="countdown-container" id="countdownContainer">
					    <div class="countdown-item">
						<div class="countdown-label">优惠还剩最后:</div>
					        <div class="countdown-value" id="countdownHours">00</div>
					        <div class="countdown-label">时</div>
					    </div>
					    <div class="countdown-item">
					        <div class="countdown-value" id="countdownMinutes">30</div>
					        <div class="countdown-label">分</div>
					    </div>
					    <div class="countdown-item">
					        <div class="countdown-value" id="countdownSeconds">00</div>
					        <div class="countdown-label">秒</div>
					    </div>
					</div>
					<div class="zffss">支付方式</div>
	                 <div class="radio-group">
	                         <label>
								 <input type="radio" id="payment_method" name="payment_method" value="wxpay" checked="">
	                             <div class="radio-img">
	                                 <img src="../result/images/wxpay.png" alt="微信支付" onclick="toggleRadio('wxpay')">
	                             </div>
	                         </label>
	                         <label>
	                             <label><input type="radio" id="payment_method" name="payment_method" value="alipay">
	                             <div class="radio-img">
	                                 <img src="../result/images/alipay.png" alt="支付宝" onclick="toggleRadio('alipay')">
	                             </div>
	                         </label>
	                     </div>
					<input type="hidden" id="payment_time" name="payment_time" value="<?php echo $currentTime; ?>" readonly>
	        <div class="popup-footer">
	            <button onclick="submitPayment()">确认支付</button>
	        </div>
		    </form>
		     <a href="upload.php"><div class="countdown-label">不想打赏？做任务免费进群></div></a>
	    </div>
	
	<a href="../issues/complaint.html"><div class="task-issues">投诉</div></a>
	<a href="upload.php"><div class="task-button">做 任 务 免 费 进</div></a>
	<div class="circle-button" onclick="openModal()">
	      <img src="../result/images/kefu_1.png"/>
	</div>
	
	<div class="modal-overlay" id="modal-overlay"></div>
	    <div class="modal" id="modal">
	        <div class="modal-header">
	            <h3>扫码添加客服</h3>
	            <span class="close-btn" onclick="closeModal()">×</span>
	        </div>
	        <div class="modal-content">
	            <img src="<?php echo $customer_service_image; ?>" alt="kefu">
	        </div>
	    </div>

       <div class="container0">
        <div class="content-a">
			<div class="fookj">
				<img src="<?php echo $group_avatar; ?>"/>
			</div>
			<div class="pjds-5">
			     <b class="pjds"><?php echo $group_title; ?></b><br>
				 <b class="pjdp"><?php echo $sub_title; ?></b>
			</div>
		</div>
		
		<!--<div class="content-b">
			<div class="fnlkld">
				<b>群 介 绍</b>
			</div> 
			<div class="fnlkld-1">
				<p><?php echo $group_description; ?></p>
			</div>
		</div> -->
		
		<div class="content-c">
			<div class="fnlkld">
				<b>群 列 表</b>
			</div>
			 <div class="columns-container">
			     <div class="column">
			         <div class="column-content">
			             <img src="../result/images/qun_1.png" alt="Avatar" class="avatar1">
			             <div class="nickname">群1<span class="jshdj">(满)</span><br><span>500/500</span></div>
			         </div>
			     </div>
			     <div class="column">
			         <div class="column-content">
			             <img src="../result/images/qun_2.png" alt="Avatar" class="avatar1">
			             <div class="nickname">群2<span class="jshdj">(满)</span><br><span>495/500</span></div>
			         </div>
			     </div>
			     <div class="column">
			         <div class="column-content">
			             <img src="../result/images/qun_3.png" alt="Avatar" class="avatar1">
			             <div class="nickname">群3<span class="jshdj">(满)</span><br><span>498/500</span></div>
			         </div>
			     </div>
			 </div>
			
		</div>
		<div class="content-d">
			<div class="fnlkld">
				<b>已加入的成员</b>
			</div>
			 <div class="avatars-container">
			     <div class="avatar"><img src="<?php echo $pavatar1; ?>" alt="Avatar" class="avatar"></div>
			     <div class="avatar"><img src="<?php echo $pavatar2; ?>" alt="Avatar" class="avatar"></div>
			     <div class="avatar"><img src="<?php echo $pavatar3; ?>" alt="Avatar" class="avatar"></div>
			     <div class="avatar"><img src="<?php echo $pavatar4; ?>" alt="Avatar" class="avatar"></div>
			     <div class="avatar"><img src="<?php echo $pavatar5; ?>" alt="Avatar" class="avatar"></div>
			     <div class="avatar"><img src="<?php echo $pavatar6; ?>" alt="Avatar" class="avatar"></div>
			     <div class="avatar"><img src="<?php echo $pavatar7; ?>" alt="Avatar" class="avatar"></div> 
			 </div>
		</div>
		<div class="content-e">
			<div class="fnlkld">
				<b>部分展示</b>
			</div>
			   <!-- 视频展示部分-->
				<div class="containersy">
				    <div class="video-item" onclick="showVideo(this)">
				        <img src="../result/video/shipin1.png" alt="视频1封面">
				        <div class="video-intro">部分展示</div>
				        <video class="video-player" src="../result/video/1.mp4"></video>
				        <div class="play-button">
				            <img src="../result/images/bf.png" alt="播放按钮">
				        </div>
				    </div>
				    <div class="video-item" onclick="showVideo(this)">
				        <img src="../result/video/shipin2.png" alt="视频2封面">
				        <div class="video-intro">部分展示</div>
				        <video class="video-player" src="../result/video/2.mp4"></video>
				        <div class="play-button">
				            <img src="../result/images/bf.png" alt="播放按钮">
				        </div>
				    </div>
				    <div class="video-item" onclick="showVideo(this)">
				        <img src="../result/video/shipin3.png" alt="视频3封面">
				        <div class="video-intro">部分展示</div>
				        <video class="video-player" src="../result/video/3.mp4"></video>
				        <div class="play-button">
				            <img src="../result/images/bf.png" alt="播放按钮">
				        </div>
				    </div>
				    <div class="video-item" onclick="showVideo(this)">
				        <img src="../result/video/shipin4.png" alt="视频4封面">
				        <div class="video-intro">部分展示</div>
				        <video class="video-player" src="../result/video/4.mp4"></video>
				        <div class="play-button">
				            <img src="../result/images/bf.png" alt="播放按钮">
				        </div>
				    </div>
				</div>
				
				<!--<div class="popupsp" id="popupsp">
				    <h3>打赏观看</h3>
				    <p>打赏<?php echo $entry_price; ?>元进vip群看完整版</p>
				    <button class="button-qx" onclick="closePopupsp()">取消</button>&hairsp;&hairsp;<button class="button-qd" onclick="openPopup()">确定</button>
				</div>
                <div class="overlaysp" id="overlaysp"></div>-->

                 <!--图片部分容展示-->
		        <div class="vijds">
					 <?php if (!empty($group_images)) {
					        foreach ($group_images as $image_path) {
					            echo '<img src="'. $image_path. '" alt="展示图片">';
					        }
					    } else {
					        echo '<p>暂无图片</p>';
					    }
					    ?>
				</div>
			
			</div> 
			<div class="qunjieshao">
				<div class="qunstate"><div class="title">群简介<i style="color: red;">（付款即代表已读！）</i>
				      </div><div class="qs"><p><?php echo $group_description; ?></p></div></div>
				
				<div class="qunstate">
					<div class="title">常见问题<i style="color: red;">（付款即代表已读！）</i></div>
				    <div class="question">
						<p class="ad"><?php echo $question; ?></p>
						<p class="aw"></p>
					</div>
			    </div>
			</div>
			     <div class="yuedu-1">
			     	<div class="yuedu">阅读10万+</div>
			     	<div class="qunicon">
			     	  <div class="qunicon1">
			     		<div class="icon1"><img src="../result/images/icon1.png"><span>分享</span></div>
			     		<div class="icon1"><img src="../result/images/icon2.png"><span>收藏</span></div>
			     	  </div>
			     	  <div class="qunicon1">
			     		<div class="icon1"><img src="../result/images/icon3.jpg"><span>3659</span></div>
			     		<div class="icon1"><img src="../result/images/icon4.jpg"><span>665</span></div>
			     	  </div>
			     	</div>
			     </div>
				
				<div class="pinglun">
					<div class="qunliuyan">
					
						<div class="liuyantit">
							<p class="lyo">群友评论（精选）</p>
						</div>
						<div class="liuyanz">
							<img class="qleft" src="<?php echo $avatar1; ?>">
							<div class="qcenter"><p class="nichen"><?php echo $variable1; ?></p><div class="liuyan"><?php echo $reviews1; ?></div></div>
							<div class="qright"><img src="../result/images/icon3.jpg"><p>赞:<?php echo $number1; ?></p></div>
						</div>
						<div class="liuyanz">
							<img class="qleft" src="<?php echo $avatar2; ?>">
							<div class="qcenter"><p class="nichen"><?php echo $variable2; ?></p><div class="liuyan"><?php echo $reviews2; ?></div></div>
							<div class="qright"><img src="../result/images/icon3.jpg"><p>赞:<?php echo $number2; ?></p></div>
						</div>
						<div class="liuyanz">
							<img class="qleft" src="<?php echo $avatar3; ?>">
							<div class="qcenter"><p class="nichen"><?php echo $variable3; ?></p><div class="liuyan"><?php echo $reviews3; ?></div></div>
							<div class="qright"><img src="../result/images/icon3.jpg"><p>赞:<?php echo $number3; ?></p></div>
						</div>
						<div class="liuyanz">
							<img class="qleft" src="<?php echo $avatar4; ?>">
							<div class="qcenter"><p class="nichen"><?php echo $variable4; ?></p><div class="liuyan"><?php echo $reviews4; ?></div></div>
							<div class="qright"><img src="../result/images/icon3.jpg"><p>赞:<?php echo $number4; ?></p></div>
						</div>
						<div class="liuyanz">
							<img class="qleft" src="<?php echo $avatar5; ?>">
							<div class="qcenter"><p class="nichen"><?php echo $variable5; ?></p><div class="liuyan"><?php echo $reviews5; ?></div></div>
							<div class="qright"><img src="../result/images/icon3.jpg"><p>赞:<?php echo $number5; ?></p></div>
						</div>
					</div>
				</div>
		 <div style="width: 100%;height: 80px;"></div>
	</div>
    
</body>
<script src="../static/js/script.js"></script>
<script src="../static/js/upload.js"></script>
<script src="../static/js/visitor.js"></script>
</html>
