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
            margin: 0;
            background:
                radial-gradient(1200px 460px at 0% 0%, rgba(83, 86, 251, 0.16), transparent 55%),
                radial-gradient(900px 420px at 100% 0%, rgba(245, 57, 248, 0.12), transparent 52%),
                #f5f7ff;
            padding-bottom: 92px;
        }

        .task-settings-form {
            width: min(1180px, calc(100% - 24px));
            margin: 68px auto 24px;
            background: #fff;
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 20px;
            box-shadow: 0 20px 38px rgba(83, 86, 251, 0.12);
            padding: 20px 18px;
            box-sizing: border-box;
        }

        .form-head {
            margin-bottom: 14px;
        }

        .form-head h2 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #1f2a47;
            letter-spacing: 0.2px;
        }

        .form-intro {
            margin: 0;
            font-size: 13px;
            line-height: 1.7;
            color: #667085;
        }

        .settings-block {
            margin-top: 14px;
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 16px;
            background: #fbfbff;
            overflow: hidden;
        }

        .settings-block-head {
            margin: 0;
            padding: 12px 14px;
            background: #f2f4ff;
            border-bottom: 1px solid rgba(83, 86, 251, 0.15);
            color: #313d68;
            font-size: 15px;
            font-weight: 700;
        }

        .settings-block-body {
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .setting-field {
            border: 1px solid rgba(83, 86, 251, 0.12);
            border-radius: 14px;
            background: #fff;
            padding: 12px;
        }

        .setting-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #445079;
        }

        .setting-field input[type="text"],
        .setting-field textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 11px 12px;
            border: 1px solid #d8d6ff;
            border-radius: 12px;
            font-size: 14px;
            color: #293553;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .setting-field input[type="text"]:focus,
        .setting-field textarea:focus {
            border-color: #5356fb;
            box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.14);
        }

        .field-tip {
            margin-top: 8px;
            margin-bottom: 0;
            font-size: 12px;
            color: #98a2b3;
            line-height: 1.6;
        }

        .field-grid {
            display: grid;
            gap: 12px;
        }

        .image-upload-container {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 14px;
            border: 1px dashed rgba(83, 86, 251, 0.34);
            background: #f7f8ff;
        }

        .upload-button {
            width: 84px;
            height: 124px;
            border: 1px dashed rgba(83, 86, 251, 0.45);
            border-radius: 12px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            background: #fff;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .upload-button span {
            color: #8f95d8;
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }

        .upload-button:hover {
            border-color: #5356fb;
            box-shadow: 0 0 0 4px rgba(83, 86, 251, 0.12);
        }

        .upload-button input[type="file"] {
            display: none;
        }

        img.preview {
            width: 84px;
            height: 124px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(83, 86, 251, 0.24);
            box-shadow: 0 10px 24px rgba(83, 86, 251, 0.16);
            flex-shrink: 0;
        }

        .task-save-bar {
            position: fixed;
            left: 16px;
            right: 16px;
            bottom: 14px;
            z-index: 90;
        }

        .task-save-btn {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 12px 20px;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            box-shadow: 0 16px 30px rgba(83, 86, 251, 0.3);
        }

        #custom-modal {
            margin: 0;
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
            min-height: 102px;
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
            .task-settings-form {
                margin-top: 78px;
                padding: 24px 24px 22px;
            }

            .field-grid.two-col {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .task-save-bar {
                position: sticky;
                left: auto;
                right: auto;
                bottom: 12px;
                width: 220px;
                margin-left: auto;
                margin-top: 14px;
            }

            .task-save-btn {
                width: 220px;
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
        <div class="form-head">
            <h2>任务流程配置</h2>
            <p class="form-intro">配置前台任务文案、审核时效和示例图片。建议先保存基础文案，再上传示例图。</p>
        </div>

        <section class="settings-block">
            <h3 class="settings-block-head">任务文案</h3>
            <div class="settings-block-body">
                <div class="setting-field">
                    <label class="setting-label" for="title">任务标题</label>
                    <input type="text" id="title" name="title" placeholder="填写任务标题">
                </div>
                <div class="setting-field">
                    <label class="setting-label" for="intro">任务介绍</label>
                    <textarea class="renwu" id="intro" name="intro" placeholder="填写任务介绍"></textarea>
                </div>
                <div class="setting-field">
                    <label class="setting-label" for="requirement">任务要求</label>
                    <textarea class="renwu_mdj" id="requirement" name="requirement" placeholder="填写任务要求"></textarea>
                </div>
                <div class="field-grid two-col">
                    <div class="setting-field">
                        <label class="setting-label" for="review_time">审核时间</label>
                        <textarea class="renwu_times" id="review_time" name="review_time" placeholder="填写任务审核时间"></textarea>
                        <p class="field-tip">示例：工作日 09:00-22:00 审核，节假日顺延至次日。</p>
                    </div>
                    <div class="setting-field">
                        <label class="setting-label" for="prompt">任务数量</label>
                        <textarea class="renwu_prompt" id="prompt" name="prompt" placeholder="填写图片数量"></textarea>
                        <p class="field-tip">示例：上传 3 张清晰截图，避免图片过暗或裁剪不完整。</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-block">
            <h3 class="settings-block-head">示例素材</h3>
            <div class="settings-block-body">
                <div class="setting-field">
                    <label class="setting-label" for="download_img_input">下载图片</label>
                    <div class="image-upload-container">
                        <img id="download_img_preview" class="preview" src="" alt="下载图片预览" style="display: none;">
                        <div class="upload-button" onclick="document.getElementById('download_img_input').click()">
                            <span>+</span>
                            <input type="file" id="download_img_input" name="download_img" onchange="previewImage(this, 'download_img_preview')">
                        </div>
                    </div>
                </div>
                <div class="setting-field">
                    <label class="setting-label" for="example_img_input">任务示例图</label>
                    <div class="image-upload-container">
                        <img id="example_img_preview" class="preview" src="" alt="示例图预览" style="display: none;">
                        <div class="upload-button" onclick="document.getElementById('example_img_input').click()">
                            <span>+</span>
                            <input type="file" id="example_img_input" name="example_img" onchange="previewImage(this, 'example_img_preview')">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-block">
            <h3 class="settings-block-head">运营提示</h3>
            <div class="settings-block-body">
                <div class="setting-field">
                    <p class="field-tip">
                        建议流程：先保存文案 -> 再上传图片 -> 最后用前台页面回归检查。
                        图片建议使用清晰截图，避免压缩过度导致审核失败。
                    </p>
                </div>
            </div>
        </section>

        <div class="task-save-bar">
            <input class="task-save-btn" type="submit" value="保存任务设置">
        </div>
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


