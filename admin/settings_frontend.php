<?php
require_once 'login_check.php';  
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../static/css/admin.css">
    <title>任务设置</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fafbfc;
            margin: 0;
            padding: 10px;
            padding-bottom: 80px; /* 为底部固定按钮预留空间 */
        }

        form {
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(16, 24, 40, 0.05);
			margin-top: 40px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
			font-size: 14px;
			color: #8b8a8a;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
			outline: none;
			font-size: 14px;
        }

        /* 优化图片和上传按钮的布局 */
        .image-upload-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        /* 优化图片上传按钮样式 */
        .upload-button {
            width: 80px;
            height: 120px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            margin-left: 20px;
        }
        .upload-button span{
			color: #ccc;
			font-size: 24px;
		}
		
		.upload-button:hover {
			border-color: #0056b3;
			box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
		}

        /* 隐藏原生的文件输入框 */
        .upload-button input[type="file"] {
            display: none;
        }

        /* 图片显示样式 */
        img.preview {
            width: 80px;
            height: 120px;
            object-fit: cover;
			border-radius: 5px;
			border: 1px solid #ccc;
        }

        /* 固定在底部的更新按钮 */
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            width: calc(100% - 40px); /* 考虑页面左右的 padding */
            position: fixed;
            bottom: 20px;
            left: 20px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        #custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
			width: 80px;
			height: 40px;
            background-color: rgba(0, 0, 0, 0.5); /* 黑色半透明背景 */
            border-radius: 5px;
            color: #fff;
            text-align: center;
			font-size: 14px;
			line-height: 14px;
        }
		.renwu{
			height: 100px;
		}
		.renwu_mdj{
			height: 40px;
		}
		.renwu_times{
			height: 80px;
		}
		.renwu_prompt{
			height: 40px;
		}

		@media (min-width: 992px) {
			body {
				padding: 0;
				background: #fafbfc;
			}

			form {
				width: min(1100px, calc(100% - 64px));
				margin: 76px auto 32px;
				padding: 24px 24px 18px;
				box-sizing: border-box;
			}

			input[type="submit"] {
				position: sticky;
				bottom: 16px;
				left: auto;
				width: 220px;
				margin-left: auto;
				display: block;
				border-radius: 10px;
			}
		}
    </style>
</head>

<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">任务设置</div>
    </div>
    <form class="task-settings-form" action="settings_backend.php" method="post" enctype="multipart/form-data">
        <label for="title">任务标题:</label>
        <input type="text" id="title" name="title" placeholder="填写任务标题">

        <label for="intro">任务介绍:</label>
        <textarea class="renwu" id="intro" name="intro" placeholder="填写任务介绍"></textarea>

        <label for="requirement">任务要求:</label>
        <textarea class="renwu_mdj" id="requirement" name="requirement" placeholder="填写任务要求"></textarea>

        <label for="review_time">审核时间:</label>
        <textarea class="renwu_times" type="text" id="review_time" name="review_time" placeholder="填写任务审核时间"></textarea>

        <label for="download_img">下载图片:</label>
        <div class="image-upload-container">
            <img id="download_img_preview" class="preview" src="" alt="下载图片预览" style="display: none;">
            <div class="upload-button" onclick="document.getElementById('download_img_input').click()">
                <span>+</span>
                <input type="file" id="download_img_input" name="download_img" onchange="previewImage(this, 'download_img_preview')">
            </div>
        </div>

        <label for="example_img">任务示例图:</label>
        <div class="image-upload-container">
            <img id="example_img_preview" class="preview" src="" alt="示例图预览" style="display: none;">
            <div class="upload-button" onclick="document.getElementById('example_img_input').click()">
                <span>+</span>
                <input type="file" id="example_img_input" name="example_img" onchange="previewImage(this, 'example_img_preview')">
            </div>
        </div>

        <label for="prompt">任务数量:</label>
        <textarea class="renwu_prompt" id="prompt" name="prompt" placeholder="填写图片数量"></textarea>

        <input type="submit" value="确认更新">
    </form>

    <div id="custom-modal">
        <div class="modal-content">
            <p id="modal-message"></p>
        </div>
    </div>

    <script>
        // 使用 AJAX 从后端获取现有任务设置数据并填充表单
        window.onload = function () {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'settings_backend.php?action=get', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    document.getElementById('title').value = data.title;
                    document.getElementById('intro').value = data.intro;
                    document.getElementById('requirement').value = data.requirement;
                    document.getElementById('review_time').value = data.review_time;

                    if (data.download_img) {
                        var downloadImgPreview = document.getElementById('download_img_preview');
                        downloadImgPreview.src = "../" + data.download_img;
                        downloadImgPreview.style.display = 'inline-block';
                    }

                    if (data.example_img) {
                        var exampleImgPreview = document.getElementById('example_img_preview');
                        exampleImgPreview.src = "../" + data.example_img;
                        exampleImgPreview.style.display = 'inline-block';
                    }

                    document.getElementById('prompt').value = data.prompt;
                }
            };
            xhr.send();
        };

        function openModal(message) {
            document.getElementById('modal-message').textContent = message;
            var customModal = document.getElementById('custom-modal');
            customModal.style.display = 'flex';

            // 设置 3 秒后自动关闭弹窗
            setTimeout(function () {
                customModal.style.display = 'none';
            }, 3000);
        }

        // 图片预览函数
        function previewImage(input, previewId) {
            var preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 监听表单提交事件
        document.querySelector('form').addEventListener('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'settings_backend.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        openModal(response.message);
                    } else {
                        openModal(response.message);
                    }
                }
            };
            xhr.send(formData);
        });
    </script>
</body>

</html>
