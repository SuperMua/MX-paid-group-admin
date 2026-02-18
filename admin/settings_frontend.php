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
            background:
                radial-gradient(1150px 420px at 0% 0%, rgba(83, 86, 251, 0.14), transparent 55%),
                radial-gradient(920px 420px at 100% 0%, rgba(245, 57, 248, 0.1), transparent 50%),
                #f5f7ff;
            margin: 0;
            padding: 0;
            padding-bottom: 86px;
        }

        form {
            width: min(1140px, calc(100% - 24px));
            margin: 68px auto 24px;
            background: #fff;
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 18px;
            box-shadow: 0 18px 34px rgba(83, 86, 251, 0.12);
            padding: 20px;
            box-sizing: border-box;
        }

        .form-intro {
            margin: 0 0 14px;
            font-size: 13px;
            color: #667085;
            line-height: 1.6;
        }

        .section-title {
            margin: 0 0 8px;
            color: #1f2a47;
            font-size: 22px;
            font-weight: 700;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            color: #4a567a;
            font-weight: 600;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 11px 12px;
            margin-bottom: 12px;
            border: 1px solid #d8d6ff;
            border-radius: 12px;
            box-sizing: border-box;
            outline: none;
            font-size: 14px;
            color: #2a3653;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #5356fb;
            box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.14);
        }

        .field-tip {
            margin-top: -2px;
            margin-bottom: 12px;
            font-size: 12px;
            color: #98a2b3;
        }

        .image-upload-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            padding: 12px;
            border: 1px dashed rgba(83, 86, 251, 0.3);
            border-radius: 14px;
            background: #f9f9ff;
        }

        .upload-button {
            width: 80px;
            height: 120px;
            border: 1px dashed rgba(83, 86, 251, 0.4);
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            background: #fff;
            transition: all 0.2s ease;
        }

        .upload-button span {
            color: #8f95d8;
            font-size: 24px;
        }

        .upload-button:hover {
            border-color: #5356fb;
            box-shadow: 0 0 0 4px rgba(83, 86, 251, 0.12);
        }

        .upload-button input[type="file"] {
            display: none;
        }

        img.preview {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(83, 86, 251, 0.24);
            box-shadow: 0 10px 24px rgba(83, 86, 251, 0.16);
        }

        input[type="submit"] {
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            color: white;
            padding: 12px 22px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            width: calc(100% - 32px);
            position: fixed;
            bottom: 18px;
            left: 16px;
            right: 16px;
            font-weight: 700;
            box-shadow: 0 16px 28px rgba(83, 86, 251, 0.3);
        }

        input[type="submit"]:hover {
            filter: brightness(1.03);
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
            width: 220px;
            min-height: 44px;
            padding: 10px 12px;
            background-color: rgba(32, 37, 65, 0.86);
            border-radius: 999px;
            color: #fff;
            text-align: center;
            font-size: 13px;
            line-height: 1.4;
        }

        .renwu {
            min-height: 100px;
        }

        .renwu_mdj {
            min-height: 48px;
        }

        .renwu_times {
            min-height: 80px;
        }

        .renwu_prompt {
            min-height: 48px;
        }

        @media (min-width: 992px) {
            form {
                margin-top: 78px;
                padding: 24px 28px 20px;
            }

            input[type="submit"] {
                position: sticky;
                bottom: 16px;
                left: auto;
                right: auto;
                width: 220px;
                margin-left: auto;
                border-radius: 12px;
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
        <h2 class="section-title">任务流程配置</h2>
        <p class="form-intro">配置前台任务文案、审核时效和示例图片。建议先保存基础文案，再上传示例图。</p>
        <label for="title">任务标题:</label>
        <input type="text" id="title" name="title" placeholder="填写任务标题">

        <label for="intro">任务介绍:</label>
        <textarea class="renwu" id="intro" name="intro" placeholder="填写任务介绍"></textarea>

        <label for="requirement">任务要求:</label>
        <textarea class="renwu_mdj" id="requirement" name="requirement" placeholder="填写任务要求"></textarea>

        <label for="review_time">审核时间:</label>
        <textarea class="renwu_times" type="text" id="review_time" name="review_time" placeholder="填写任务审核时间"></textarea>
        <p class="field-tip">示例：工作日 09:00-22:00 审核，节假日顺延至次日。</p>

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
        <p class="field-tip">示例：上传 3 张清晰截图，避免图片过暗或裁剪不完整。</p>

        <input type="submit" value="保存任务设置">
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
<script src="../static/js/admin-shell.js"></script>
</body>

</html>


