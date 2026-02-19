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
                radial-gradient(1200px 460px at 0% 0%, rgba(83, 86, 251, 0.18), transparent 54%),
                radial-gradient(940px 420px at 100% 0%, rgba(245, 57, 248, 0.13), transparent 52%),
                #f4f7ff;
            padding-bottom: 96px;
        }

        .task-settings-form {
            width: min(1240px, calc(100% - 24px));
            margin: 68px auto 24px;
            padding: 18px;
            box-sizing: border-box;
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 24px 44px rgba(83, 86, 251, 0.14);
            backdrop-filter: blur(4px);
        }

        .form-head {
            padding: 20px 20px 16px;
            border-radius: 22px;
            border: 1px solid rgba(83, 86, 251, 0.18);
            background:
                radial-gradient(700px 300px at 0% 0%, rgba(83, 86, 251, 0.16), transparent 60%),
                radial-gradient(520px 240px at 100% 0%, rgba(245, 57, 248, 0.14), transparent 65%),
                #ffffff;
            margin-bottom: 14px;
        }

        .form-head h2 {
            margin: 0 0 8px;
            font-size: 26px;
            color: #1f2a47;
            letter-spacing: 0.2px;
        }

        .form-intro {
            margin: 0;
            font-size: 13px;
            line-height: 1.7;
            color: #667085;
        }

        .task-meta-badges {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .task-meta-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(83, 86, 251, 0.2);
            background: #f4f6ff;
            color: #47527a;
            padding: 7px 11px;
            font-size: 12px;
            font-weight: 700;
        }

        .task-layout {
            display: grid;
            gap: 14px;
        }

        .settings-main-col,
        .settings-side-col {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .settings-block {
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 20px;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 12px 26px rgba(83, 86, 251, 0.08);
        }

        .settings-block-head {
            margin: 0;
            padding: 14px 16px;
            border-bottom: 1px solid rgba(83, 86, 251, 0.14);
            background: linear-gradient(135deg, #eef1ff 0%, #f8f2ff 100%);
            color: #2e3a65;
            font-size: 16px;
            font-weight: 700;
        }

        .settings-block-body {
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .task-copy-grid {
            grid-template-columns: 1fr;
        }

        .setting-field {
            border: 1px solid rgba(83, 86, 251, 0.13);
            border-radius: 18px;
            background: #fcfcff;
            padding: 14px;
        }

        .setting-label {
            display: block;
            margin-bottom: 8px;
            color: #445079;
            font-size: 14px;
            font-weight: 700;
        }

        .setting-field input[type="text"],
        .setting-field textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d8d6ff;
            border-radius: 16px;
            background: #fff;
            padding: 12px 13px;
            font-size: 14px;
            color: #293553;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .setting-field textarea {
            resize: vertical;
            line-height: 1.7;
        }

        .setting-field input[type="text"]:focus,
        .setting-field textarea:focus {
            border-color: #5356fb;
            box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.16);
        }

        .field-tip {
            margin: 8px 0 0;
            color: #98a2b3;
            font-size: 12px;
            line-height: 1.6;
        }

        .field-grid {
            display: grid;
            gap: 12px;
        }

        .assets-grid {
            grid-template-columns: 1fr;
        }

        .image-upload-container {
            padding: 12px;
            border: 1px dashed rgba(83, 86, 251, 0.34);
            border-radius: 18px;
            background: linear-gradient(180deg, #f7f8ff 0%, #f9f9ff 100%);
            display: grid;
            gap: 10px;
        }

        .upload-preview-shell {
            min-height: 130px;
            border: 1px solid rgba(83, 86, 251, 0.18);
            border-radius: 16px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        img.preview {
            width: min(220px, 100%);
            max-height: 140px;
            object-fit: contain;
            border-radius: 14px;
            box-shadow: 0 10px 22px rgba(83, 86, 251, 0.16);
        }

        .upload-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .upload-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 10px 16px;
            border: 1px solid rgba(83, 86, 251, 0.22);
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 10px 18px rgba(83, 86, 251, 0.24);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .upload-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 22px rgba(83, 86, 251, 0.28);
        }

        .upload-file-name {
            color: #667085;
            font-size: 12px;
            line-height: 1.6;
        }

        .task-hidden-file {
            display: none;
        }

        .ops-card {
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 20px;
            background: #fff;
            padding: 14px;
            box-shadow: 0 12px 24px rgba(83, 86, 251, 0.08);
        }

        .ops-card h4 {
            margin: 0 0 8px;
            color: #2f3a66;
            font-size: 15px;
        }

        .ops-card p {
            margin: 0;
            color: #7b859f;
            font-size: 13px;
            line-height: 1.7;
        }

        .ops-list {
            margin: 0;
            padding-left: 18px;
            color: #6b728a;
            font-size: 13px;
            line-height: 1.7;
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
            padding: 13px 20px;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            box-shadow: 0 16px 30px rgba(83, 86, 251, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .task-save-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(83, 86, 251, 0.35);
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
            width: 260px;
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
            min-height: 120px;
        }

        .renwu_mdj,
        .renwu_prompt {
            min-height: 62px;
        }

        .renwu_times {
            min-height: 98px;
        }

        @media (min-width: 992px) {
            .task-settings-form {
                margin-top: 78px;
                padding: 22px;
            }

            .task-layout {
                grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
            }

            .field-grid.two-col {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .assets-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .task-copy-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .task-copy-grid .full-width {
                grid-column: 1 / -1;
            }

            .task-save-bar {
                position: sticky;
                left: auto;
                right: auto;
                bottom: 12px;
                width: 230px;
                margin-left: auto;
                margin-top: 4px;
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
            <div class="task-meta-badges">
                <span class="task-meta-badge">支持手机端与 PC 端自适应</span>
                <span class="task-meta-badge">保存后前台实时生效</span>
                <span class="task-meta-badge">建议先文案后图片</span>
            </div>
        </div>

        <div class="task-layout">
            <div class="settings-main-col">
                <section class="settings-block">
                    <h3 class="settings-block-head">任务文案</h3>
                    <div class="settings-block-body task-copy-grid">
                        <div class="setting-field">
                            <label class="setting-label" for="title">任务标题</label>
                            <input type="text" id="title" name="title" placeholder="填写任务标题">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label" for="requirement">任务要求</label>
                            <textarea class="renwu_mdj" id="requirement" name="requirement" placeholder="填写任务要求"></textarea>
                        </div>
                        <div class="setting-field full-width">
                            <label class="setting-label" for="intro">任务介绍</label>
                            <textarea class="renwu" id="intro" name="intro" placeholder="填写任务介绍"></textarea>
                        </div>
                        <div class="field-grid two-col full-width">
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
                    <div class="settings-block-body assets-grid">
                        <div class="setting-field">
                            <label class="setting-label" for="download_img_input">下载图片</label>
                            <div class="image-upload-container">
                                <div class="upload-preview-shell">
                                    <img id="download_img_preview" class="preview" src="" alt="下载图片预览" style="display: none;">
                                </div>
                                <div class="upload-row">
                                    <label class="upload-pill" for="download_img_input">上传下载图片</label>
                                    <span class="upload-file-name" id="download_img_name">未选择文件</span>
                                </div>
                                <input class="task-hidden-file" type="file" id="download_img_input" name="download_img" onchange="previewImage(this, 'download_img_preview')">
                            </div>
                        </div>
                        <div class="setting-field">
                            <label class="setting-label" for="example_img_input">任务示例图</label>
                            <div class="image-upload-container">
                                <div class="upload-preview-shell">
                                    <img id="example_img_preview" class="preview" src="" alt="示例图预览" style="display: none;">
                                </div>
                                <div class="upload-row">
                                    <label class="upload-pill" for="example_img_input">上传示例图片</label>
                                    <span class="upload-file-name" id="example_img_name">未选择文件</span>
                                </div>
                                <input class="task-hidden-file" type="file" id="example_img_input" name="example_img" onchange="previewImage(this, 'example_img_preview')">
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="settings-side-col">
                <section class="ops-card">
                    <h4>发布建议</h4>
                    <p>建议流程：先保存文案 -> 再上传图片 -> 最后用前台页面回归检查。图片建议使用清晰截图，避免压缩过度导致审核失败。</p>
                </section>
                <section class="ops-card">
                    <h4>质检清单</h4>
                    <ul class="ops-list">
                        <li>任务标题与文案是否和当前活动一致。</li>
                        <li>审核时间是否标注完整（工作日/节假日）。</li>
                        <li>示例图是否清晰且无遮挡。</li>
                    </ul>
                </section>
                <section class="ops-card">
                    <h4>回滚策略</h4>
                    <p>若上线后发现文案问题，可直接回填上一版内容并再次保存，系统会即时生效。</p>
                </section>
            </aside>
        </div>

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
        (function () {
            const form = document.querySelector('.task-settings-form');
            if (!form || form.dataset.boundTaskSettings === '1') {
                return;
            }
            form.dataset.boundTaskSettings = '1';

            const submitButton = form.querySelector('.task-save-btn');

            function openModal(message, isError) {
                const modal = document.getElementById('custom-modal');
                const content = modal ? modal.querySelector('.modal-content') : null;
                document.getElementById('modal-message').textContent = message;
                if (content) {
                    content.style.backgroundColor = isError ? 'rgba(185, 28, 28, 0.9)' : 'rgba(32, 37, 65, 0.86)';
                }
                if (modal) {
                    modal.style.display = 'flex';
                }
                setTimeout(function () {
                    if (modal) {
                        modal.style.display = 'none';
                    }
                }, 2200);
            }

            function fillTaskSettings(data) {
                document.getElementById('title').value = data.title || '';
                document.getElementById('intro').value = data.intro || '';
                document.getElementById('requirement').value = data.requirement || '';
                document.getElementById('review_time').value = data.review_time || '';
                document.getElementById('prompt').value = data.prompt || '';

                if (data.download_img) {
                    const downloadImgPreview = document.getElementById('download_img_preview');
                    downloadImgPreview.src = '../' + data.download_img;
                    downloadImgPreview.style.display = 'inline-block';
                    const downloadName = document.getElementById('download_img_name');
                    if (downloadName) {
                        downloadName.textContent = data.download_img.split('/').pop();
                    }
                }

                if (data.example_img) {
                    const exampleImgPreview = document.getElementById('example_img_preview');
                    exampleImgPreview.src = '../' + data.example_img;
                    exampleImgPreview.style.display = 'inline-block';
                    const exampleName = document.getElementById('example_img_name');
                    if (exampleName) {
                        exampleName.textContent = data.example_img.split('/').pop();
                    }
                }
            }

            function loadTaskSettings() {
                fetch('settings_backend.php?action=get', { cache: 'no-store' })
                    .then(function (response) { return response.json(); })
                    .then(function (data) { fillTaskSettings(data || {}); })
                    .catch(function (error) {
                        console.error('加载任务设置失败:', error);
                        openModal('读取任务设置失败，请刷新重试', true);
                    });
            }

            window.previewImage = function (input, previewId) {
                const preview = document.getElementById(previewId);
                if (!preview || !input.files || !input.files[0]) {
                    return;
                }
                const fileLabelId = input.id === 'download_img_input' ? 'download_img_name' : 'example_img_name';
                const fileLabel = document.getElementById(fileLabelId);
                if (fileLabel) {
                    fileLabel.textContent = input.files[0].name;
                }
                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.src = event.target.result;
                    preview.style.display = 'inline-block';
                };
                reader.readAsDataURL(input.files[0]);
            };

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(form);

                submitButton.disabled = true;
                submitButton.value = '保存中...';

                fetch('settings_backend.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        const success = payload && payload.status === 'success';
                        const message = payload && payload.message ? payload.message : (success ? '保存成功' : '保存失败');
                        openModal(message, !success);
                    })
                    .catch(function (error) {
                        console.error('保存任务设置失败:', error);
                        openModal('保存失败，请稍后重试', true);
                    })
                    .finally(function () {
                        submitButton.disabled = false;
                        submitButton.value = '保存任务设置';
                    });
            });

            loadTaskSettings();
        })();
    </script>
<script src="../static/js/admin-shell.js"></script>
</body>

</html>


