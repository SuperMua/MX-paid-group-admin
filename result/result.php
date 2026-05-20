<?php
require_once 'yz.php'; //验证ip
require_once __DIR__ . '/../public/settings_data.php';
require_once __DIR__ . '/../public/image_upload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>入群二维码</title>
  <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* 确保内容至少填满视口高度 */
            background-image: url('../result/images/qunbj.jpg'); /* 设置全屏背景图片 */
            background-size: cover; /* 背景图片覆盖整个屏幕 */
            background-position: center; /* 背景图片居中 */
            background-repeat: no-repeat; /* 背景图片不重复 */
            background-attachment: fixed; /* 背景图片固定在视口中 */
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8); /* 半透明背景 */
            border-radius: 8px; /* 边框圆弧8px */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 80%;
            max-width: 400px;
            position: relative; /* 设置相对定位，以便绝对定位的按钮相对于它定位 */
            margin-top: -60px; /* 向上移动40px */
            /*box-shadow: 0 0 20px #666;*/
        }
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px; /* 图片边框圆弧8px */
            overflow: hidden; /* 隐藏超出边框的部分 */
            position: relative; /* 设置相对定位，以便绝对定位的按钮相对于它定位 */
        }
        .image-container img {
            max-width: 100%;
            height: auto;
        }
        .save-button {
            position: absolute; /* 设置绝对定位 */
            bottom: 40px; /* 距离底部40px */
            left: 50%; /* 水平居中 */
            transform: translateX(-50%); /* 水平居中微调 */
            padding: 8px 16px;
            background: linear-gradient(to bottom, #c7f9f9, #1bcaff);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            z-index: 9999; /* 确保按钮在最上层 */
        }
        .save-button:hover {
            background-color: #0056b3;
        }
		/* 客服按钮位置 */
		 .circle-button {
		 	width: 50px;
		 	height: 50px;
		 	background-color: rgba(0, 0, 0, 0.5);
		 	border-radius: 8px;
		
		 	position: fixed;
		 	/* 固定定位 */
		 	bottom: 20%;
		 	/* 距离顶部30% */
		 	right: 0px;
		 	/* 距离右侧10px */
		 	z-index: 99;
		 	/* 确保在最上层 */
		 	display: flex;
		 	align-items: center;
		 	justify-content: center;
		 	cursor: pointer;
		 }
		
		 .circle-button img {
		 	width: 50px;
		 	height: 50px;
		 }
		 /* 遮罩层样式 */
		 .modal-overlay {
		     display: none;
		     position: fixed;
		     top: 0;
		     left: 0;
		     width: 100%;
		     height: 100%;
		     background-color: rgba(0, 0, 0, 0.5);
		     z-index: 1000;
		 }
		 
		 /* 弹窗样式 */
		 .modal {
		     display: none;
		     position: fixed;
		     top: 50%;
		     left: 50%;
		     transform: translate(-50%, -50%);
		     background-color: white;
		     padding: 20px;
		     border-radius: 10px;
		     z-index: 1001;
		     width: 300px;
		     text-align: center;
		 }
		 
		 /* 弹窗头部样式 */
		 .modal-header {
		     display: flex;
		     justify-content: space-between;
		     align-items: center;
		     margin-bottom: 20px;
		 }
		 
		 /* 关闭按钮样式 */
		 .close-btn {
		     cursor: pointer;
		     width: 20px;
		     height: 20px;
		     position: relative;
		     background: none;
		     border: none;
		     padding: 0;
		     margin: 0;
		     display: inline-block;
		     line-height: 1;
		 }
		 
		 .close-btn::before,
		 .close-btn::after {
		     content: '';
		     position: absolute;
		     top: 50%;
		     left: 50%;
		     width: 100%;
		     height: 2px;
		     background-color: #333;
		     transform: translate(-50%, -50%);
		 }
		 
		 .close-btn::before {
		     transform: translate(-50%, -50%) rotate(45deg);
		 }
		 
		 .close-btn::after {
		     transform: translate(-50%, -50%) rotate(-45deg);
		 }
		 
		 .close-btn:hover::before,
		 .close-btn:hover::after {
		     background-color: #ff0000;
		 }
		 
		 /* 弹窗内容样式 */
		 .modal-content img {
		     max-width: 100%;
		     height: auto;
		 }
    </style>
</head>
<body>
    <div class="circle-button" onclick="openModal()">
          <img src="../result/images/kefu_1.png"/>
    </div>
	<!-- 弹窗 -->
	<div class="modal-overlay" id="modal-overlay"></div>
	    <div class="modal" id="modal">
	        <div class="modal-header">
	            <h3>扫码添加客服</h3>
	            <span class="close-btn" onclick="closeModal()"></span>
	        </div>
	        <div class="modal-content">
	            <img src="<?php echo $customer_service_image; ?>" alt="kefu">
	        </div>
	    </div>
	<!-- 群二维码 -->
    <div class="container">
        <div class="image-container">
          <img id="popupImage" src="" alt="不显示二维码请联系客服">
        </div>
    </div>
    <!--<button class="save-button" onclick="saveImage()">保存图片</button>-->
    <script>
        function saveImage() {
            const imgSrc = document.getElementById('imageToSave').src;
            const link = document.createElement('a');
            link.href = imgSrc;
            link.download = 'qun.png'; // 设置下载的文件名
            link.click();
        }
    </script>
	<script>   
	    var token = "<?php echo isset($_SESSION['image_token'])? $_SESSION['image_token'] : '';?>";
	    fetch('../public/new.php?token=' + token)
	    .then(res => {
	            if (res.ok) {
	                return res.blob(); // 使用 blob 类型处理图像数据
	            } else {
	                throw new Error('无权访问此图像');
	            }
	        })
	    .then(blob => {
	            var imageUrl = URL.createObjectURL(blob);
	             document.getElementById('popupImage').src = imageUrl;
	        })
	    .catch(error => {
	            alert(error.message);
	        });
			
		// 打开弹窗
		function openModal() {
		    document.getElementById('modal-overlay').style.display = 'block';
		    document.getElementById('modal').style.display = 'block';
		}
		
		// 关闭弹窗
		function closeModal() {
		    document.getElementById('modal-overlay').style.display = 'none';
		    document.getElementById('modal').style.display = 'none';
		}
		
		// 点击遮罩层关闭弹窗
		document.getElementById('modal-overlay').addEventListener('click', closeModal);
	               
	    </script>
</body>

</html>