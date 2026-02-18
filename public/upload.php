<?php
require_once 'image_upload.php';
require_once 'settings_data.php';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../static/css/upload.css">
	<script src="../static/js/upload.js"></script>
    <title>任务详情</title>
    <style></style>
</head>
<body>
  <div class="navbar">
           <a class="back-button left-arrow" href="#" onclick="history.length>1 ? history.back() : location.href=document.referrer||'/';"></a>
          <p class="title">任务详情</p>
      </div>
  <div class="task-container">
    <div class="task-header">
      <b><?php echo $title; ?></b>
    </div>
	<div class="task-idop">3646人已完成&nbsp;&nbsp;&nbsp;&nbsp;平均完成时长3分钟</div>
    <div class="task-info copsl-ndh">
      <b class="biaoti">任务介绍:</b>
	  <p><?php echo $intro; ?></p>
    </div>
	<div class="task-info">
	  <b class="biaoti">图片预览:</b>
	    <div class="tupian task-tupian">
		   <img src="<?php echo $download_img; ?>" alt="" onclick="window.mokuai.tanChuang.openPopup1()" id="imgSrc">
		   <button class="tupianxz example-btn" onclick="window.mokuai.tuPian.downloadImage()">下载图片</button>
		</div>
	  <p>点击图片放大查看</p>
	</div>
	<div class="task-info">
	  <b class="biaoti">任务要求:</b>
	  <p><?php echo $requirement; ?></p>
	</div>
	<div class="task-info">
	  <b class="biaoti">查看示例:</b>
	  <button class="example-btn" onclick="window.mokuai.tanChuang.openPopup2()">点击查看</button>
	</div>
	<div class="task-info">
	  <b class="biaoti">审核时间:</b>
	  <p><?php echo $review_time; ?></p>
	</div>
	<div class="task-info copsl-ndh">
	       <p class="task-scjt">请上传<?php echo $prompt; ?>张符合任务要求的图片</p>
		   <div class="selected-files1" id="upload-status"></div>
		   <div class="selected-files" id="selected-count"></div>
		   <div class="selected-files" style="color:red;" id="selected-count1"></div>
		   <div class="image-container">
	           <div class="select-button" onclick="imageUploader.selectImage()">
	               <span>+</span>
	           </div>
	       </div>
	       <button class="example-apo" id="upload-button" onclick="imageUploader.submitImages()">确认上传</button>
	</div>
   </div>
   
	<!-- 弹窗放大查看1 -->
	<div class="mask" id="mask1">
	        <div class="popup" id="popup1">
	            <span class="close-btn" onclick="window.mokuai.tanChuang.closePopup1()">×</span>
	            <img src="<?php echo $download_img; ?>" alt="">
	        </div>
	    </div>
<!-- 弹窗2 -->
   <div class="mask" id="mask2">
           <div class="popup" id="popup2">
               <span class="close-btn" onclick="window.mokuai.tanChuang.closePopup2()">×</span>
               <img src="<?php echo $example_img; ?>" alt="">
           </div>
       </div>

	   <?php if ($hasUpload) {?>
	       <div id="myModal" class="modal">
	           <div class="modal-content">
	               <span class="close">&times;</span>
	               <div class="modal-message">您已提交审核</div>
	               <button class="buttonlk" onclick="window.location.href='success_page.php'">查看进度</button>
	           </div>
	       </div>
	   <?php }?>	   
</body>

<script src="../static/js/visitor.js"></script>

</html>