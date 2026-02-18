<?php
require_once 'login_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>品牌设置</title>
    <link rel="stylesheet" href="../static/css/admin.css">
    <style>
        body {
            margin: 0;
            background:
                radial-gradient(1150px 460px at 0% 0%, rgba(83, 86, 251, 0.14), transparent 55%),
                radial-gradient(900px 420px at 100% 0%, rgba(245, 57, 248, 0.1), transparent 50%),
                #f5f7ff;
        }

        .brand-settings-card {
            margin: 68px auto 24px;
            width: min(980px, calc(100% - 24px));
            background: #fff;
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 18px;
            box-shadow: 0 16px 32px rgba(83, 86, 251, 0.12);
            padding: 20px 18px;
        }

        .brand-settings-title {
            margin: 0 0 8px;
            color: #2a3352;
            font-size: 22px;
        }

        .brand-settings-intro {
            margin: 0 0 18px;
            color: #667085;
            font-size: 13px;
            line-height: 1.7;
        }

        .brand-form-grid {
            display: grid;
            gap: 14px;
        }

        .brand-form-item label {
            display: block;
            margin-bottom: 8px;
            color: #4a567a;
            font-size: 14px;
            font-weight: 600;
        }

        .brand-form-item input[type="text"] {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d8d6ff;
            border-radius: 10px;
            font-size: 14px;
        }

        .brand-upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }

        .brand-upload-card {
            border: 1px solid rgba(83, 86, 251, 0.16);
            border-radius: 14px;
            background: #f8f7ff;
            padding: 12px;
        }

        .brand-upload-preview {
            width: 100%;
            height: 120px;
            border-radius: 10px;
            border: 1px dashed rgba(83, 86, 251, 0.3);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .brand-upload-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .brand-upload-empty {
            color: #98a2b3;
            font-size: 13px;
        }

        .brand-file-trigger {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            box-shadow: 0 12px 20px rgba(83, 86, 251, 0.26);
            cursor: pointer;
        }

        .brand-file-input {
            display: none;
        }

        .brand-action-row {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
        }

        .brand-save-btn {
            min-width: 160px;
            padding: 12px 18px;
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
            box-shadow: 0 12px 24px rgba(83, 86, 251, 0.28);
            cursor: pointer;
        }

        .brand-toast {
            margin-top: 10px;
            font-size: 13px;
            color: #5356fb;
        }

        @media (min-width: 992px) {
            .brand-settings-card {
                margin-top: 78px;
                padding: 24px 26px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="settings.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">品牌设置</div>
    </div>

    <section class="brand-settings-card">
        <h2 class="brand-settings-title">品牌与图标配置</h2>
        <p class="brand-settings-intro">
            可自定义后台品牌名称、左侧品牌 Logo、浏览器标签页图标（Favicon）。保存后后台页面自动生效。
        </p>

        <form id="brandSettingsForm">
            <div class="brand-form-grid">
                <div class="brand-form-item">
                    <label for="brand_name">品牌名称</label>
                    <input type="text" id="brand_name" name="brand_name" maxlength="120" placeholder="例如：我的运营后台">
                </div>

                <div class="brand-upload-grid">
                    <div class="brand-upload-card">
                        <div class="brand-upload-preview" id="logoPreview">
                            <span class="brand-upload-empty">暂无 Logo</span>
                        </div>
                        <button class="brand-file-trigger" type="button" id="logoTrigger">上传品牌 Logo</button>
                        <input class="brand-file-input" type="file" id="logoFile" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg,.ico">
                    </div>

                    <div class="brand-upload-card">
                        <div class="brand-upload-preview" id="faviconPreview">
                            <span class="brand-upload-empty">暂无图标</span>
                        </div>
                        <button class="brand-file-trigger" type="button" id="faviconTrigger">上传顶部图标</button>
                        <input class="brand-file-input" type="file" id="faviconFile" name="favicon" accept=".png,.jpg,.jpeg,.webp,.svg,.ico">
                    </div>
                </div>
            </div>

            <div class="brand-action-row">
                <button class="brand-save-btn" type="submit" id="saveButton">保存品牌配置</button>
            </div>
            <div class="brand-toast" id="brandToast"></div>
        </form>
    </section>

    <script>
        const form = document.getElementById('brandSettingsForm');
        const brandNameInput = document.getElementById('brand_name');
        const logoFileInput = document.getElementById('logoFile');
        const faviconFileInput = document.getElementById('faviconFile');
        const logoPreview = document.getElementById('logoPreview');
        const faviconPreview = document.getElementById('faviconPreview');
        const toast = document.getElementById('brandToast');
        const saveButton = document.getElementById('saveButton');

        document.getElementById('logoTrigger').addEventListener('click', () => logoFileInput.click());
        document.getElementById('faviconTrigger').addEventListener('click', () => faviconFileInput.click());

        function setPreview(container, imagePath, fallbackText) {
            container.innerHTML = '';
            if (!imagePath) {
                const text = document.createElement('span');
                text.className = 'brand-upload-empty';
                text.textContent = fallbackText;
                container.appendChild(text);
                return;
            }
            const image = document.createElement('img');
            image.src = imagePath;
            image.alt = fallbackText;
            container.appendChild(image);
        }

        function previewLocalFile(input, target, fallbackText) {
            const file = input.files && input.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => setPreview(target, event.target.result, fallbackText);
            reader.readAsDataURL(file);
        }

        async function loadBrandSettings() {
            const response = await fetch('brand_settings_api.php');
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.message || '读取品牌配置失败');
            }
            const data = payload.data || {};
            brandNameInput.value = data.brand_name || '';
            setPreview(logoPreview, data.logo_path || '', '暂无 Logo');
            setPreview(faviconPreview, data.favicon_path || '', '暂无图标');
        }

        logoFileInput.addEventListener('change', () => previewLocalFile(logoFileInput, logoPreview, '暂无 Logo'));
        faviconFileInput.addEventListener('change', () => previewLocalFile(faviconFileInput, faviconPreview, '暂无图标'));

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            toast.textContent = '';
            saveButton.disabled = true;
            saveButton.textContent = '保存中...';

            const formData = new FormData();
            formData.append('brand_name', brandNameInput.value.trim());
            if (logoFileInput.files && logoFileInput.files[0]) {
                formData.append('logo', logoFileInput.files[0]);
            }
            if (faviconFileInput.files && faviconFileInput.files[0]) {
                formData.append('favicon', faviconFileInput.files[0]);
            }

            try {
                const response = await fetch('brand_settings_api.php', {
                    method: 'POST',
                    body: formData
                });
                const payload = await response.json();
                if (!payload.success) {
                    throw new Error(payload.message || '保存失败');
                }

                toast.textContent = payload.message || '保存成功';
                const data = payload.data || {};
                setPreview(logoPreview, data.logo_path || '', '暂无 Logo');
                setPreview(faviconPreview, data.favicon_path || '', '暂无图标');
                logoFileInput.value = '';
                faviconFileInput.value = '';
            } catch (error) {
                toast.textContent = error.message || '请求失败，请稍后重试';
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = '保存品牌配置';
            }
        });

        loadBrandSettings().catch((error) => {
            toast.textContent = error.message || '初始化失败';
        });
    </script>
    <script src="../static/js/admin-shell.js"></script>
</body>
</html>
