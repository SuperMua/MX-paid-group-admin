<?php
require_once 'login_check.php'; 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>模版一设置</title>
    <link rel="stylesheet" href="../static/css/admin.css">
    <link rel="stylesheet" href="../static/css/settings.css">
</head>
<body>
<div class="navbar">
   <a class="back-button left-arrow" href="moban.php"></a>
    <div class="title">模版一设置</div>
</div>
<div class="container select-buttons-container">
    <form id="settingsForm" method="post" enctype="multipart/form-data" action="settings_crud.php">
        <div class="group-info-form">
            <div class="group-info-item">
                <label for="group_title">群名称</label>
                <input type="text" id="group_title" name="group_title" required placeholder="">
            </div>
            <div class="group-info-item">
                <label for="sub_title">副标题</label>
                <input type="text" id="sub_title" name="sub_title" required  placeholder="">
            </div>
			<div class="group-info-item">
			    <label for="original_price">入群原价</label>
			    <input type="number" id="original_price" name="original_price" step="0.1" required>
			</div>
			<div class="group-info-item">
			    <label for="entry_price">优惠价</label>
			    <input type="number" id="entry_price" name="entry_price" step="0.1" required>
			</div>
			<div class="group-info-item">
			    <label for="ordering">底部按钮</label>
			    <input type="text" id="ordering" name="ordering" required  placeholder="">
			</div>
        </div>
        <div class="form-group">
            <label for="group_avatar">群头像</label>
            <div class="image-container">
                <img id="group_avatar_preview" src="" alt="群头像" style="width:80px;height:80px;">
                <div class="select-button">
                    <input type="file" name="group_avatar" id="group_avatar">
                    <span >+</span>
                </div>
            </div>
        </div>
        <div class="form-group" style=" display: none;">
            <label for="customer_service_image">客服二维码</label>
            <div class="image-container">
                <img id="customer_service_image_preview" src="" alt="客服二维码" style="width:80px;height:120px;">
                <div class="select-button">
                    <input type="file" id="customer_service_image" name="customer_service_image">
                    <span>+</span>
                </div>
            </div>
        </div>
        <div class="form-group" style=" display: none;">
            <label for="qr_code">入群二维码</label>
            <div class="image-container">
                <img id="qr_code_preview" src="" alt="入群二维码" style="width:80px;height:120px;">
                <div class="select-button">
                    <input type="file" id="qr_code" name="qr_code">
                    <span >+</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="warm_tip">首页弹窗提示</label>
            <textarea id="warm_tip" name="warm_tip" rows="4" cols="50"></textarea>
        </div>
        <div class="form-group">
            <label for="group_images">部分展示(图)</label>
            <div class="image-list" id="group_images_list"></div>
            <div class="select-button">
                <input type="file" id="group_images_input" name="group_images[]" multiple> 
                <span >+</span>
            </div>
        </div>
        <div class="form-group">
            <label for="group_description">群简介</label>
            <textarea id="group_description" name="group_description" rows="4" cols="50" required></textarea>
        </div>
        <div class="form-group">
            <label for="question">常见问题</label>
            <textarea id="question" name="question" rows="4" cols="50" required></textarea>
        </div>
		<div class="group-info-form">
		    <div class="group-info-item">
		        <label for="group_title">网友评论</label>
		    </div>
		    <div class="group-info-item">
		        <label for="reviews1">网友1</label>
		        <input type="text" id="reviews1" name="reviews1" required placeholder="">
		    </div>
			<div class="group-info-item">
			    <label for="reviews2">网友2</label>
			    <input type="text" id="reviews2" name="reviews2" required placeholder="">
			</div>
			<div class="group-info-item">
			    <label for="reviews3">网友3</label>
			    <input type="text" id="reviews3" name="reviews3" required placeholder="">
			</div>
			<div class="group-info-item">
			    <label for="reviews4">网友4</label>
			    <input type="text" id="reviews4" name="reviews4" required placeholder="">
			</div>
			<div class="group-info-item">
			    <label for="reviews5">网友5</label>
			    <input type="text" id="reviews5" name="reviews5" required placeholder="">
			</div>
		</div>
        <div class="button-container">
            <input type="hidden" id="id" name="id">
            <button type="submit" class="save-button">保存设置</button>
        </div>
    </form>
    <div class="success-message" id="success-message">保存成功</div>	
</div>
<script src="../static/js/settings.js"></script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>


