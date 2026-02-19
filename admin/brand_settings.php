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
        .brand-form-grid {
            display: grid;
            gap: 12px;
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

        .brand-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(83, 86, 251, 0.16);
            background: #fff;
        }

        .brand-preview-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            background: linear-gradient(135deg, #5356fb 0%, #f539f8 100%);
        }

        .brand-preview-name {
            font-size: 14px;
            font-weight: 700;
            color: #344054;
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
            border-radius: 999px;
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
            margin-top: 12px;
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
            margin-top: 8px;
            font-size: 13px;
            line-height: 1.6;
            color: #5356fb;
        }
    </style>
</head>
<body class="settings-pro-body">
    <div class="navbar">
        <a class="back-button left-arrow" href="settings.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
        <div class="title">品牌设置</div>
    </div>

    <section class="settings-pro-shell">
        <header class="settings-pro-header">
            <h2 class="settings-pro-title">品牌与图标配置</h2>
            <p class="settings-pro-subtitle">
            可自定义后台品牌名称、左侧品牌 Logo、浏览器标签页图标（Favicon）。保存后后台页面自动生效。
            </p>
        </header>

        <form id="brandSettingsForm">
            <div class="brand-form-grid">
                <div class="settings-pro-card">
                    <h3>品牌名称</h3>
                    <div class="settings-pro-field">
                        <label for="brand_name">品牌名称</label>
                        <input class="settings-pro-input" type="text" id="brand_name" name="brand_name" maxlength="120" placeholder="例如：我的运营后台">
                    </div>
                    <div class="brand-preview">
                        <span class="brand-preview-badge" id="brandNameBadge">后台</span>
                        <span class="brand-preview-name" id="brandNamePreview">付费进群系统</span>
                    </div>
                </div>

                <div class="settings-pro-card">
                    <h3>品牌素材</h3>
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
            </div>

            <div class="settings-pro-actions">
                <button class="settings-pro-primary" type="submit" id="saveButton">保存品牌配置</button>
            </div>
            <div class="brand-toast" id="brandToast"></div>
        </form>
    </section>

    <script>
        (function () {
        const form = document.getElementById('brandSettingsForm');
        if (!form || form.dataset.boundBrandSettingsPage === '1') {
            return;
        }
        form.dataset.boundBrandSettingsPage = '1';

        const brandNameInput = document.getElementById('brand_name');
        const logoFileInput = document.getElementById('logoFile');
        const faviconFileInput = document.getElementById('faviconFile');
        const logoPreview = document.getElementById('logoPreview');
        const faviconPreview = document.getElementById('faviconPreview');
        const toast = document.getElementById('brandToast');
        const saveButton = document.getElementById('saveButton');
        const brandNamePreview = document.getElementById('brandNamePreview');
        const brandNameBadge = document.getElementById('brandNameBadge');

        function syncBrandPreview() {
            const value = (brandNameInput.value || '').trim();
            const finalName = value || '付费进群系统';
            brandNamePreview.textContent = finalName;
            brandNameBadge.textContent = finalName.replace(/\s+/g, '').slice(0, 2) || '后台';
        }

        document.getElementById('logoTrigger').addEventListener('click', () => logoFileInput.click());
        document.getElementById('faviconTrigger').addEventListener('click', () => faviconFileInput.click());
        brandNameInput.addEventListener('input', syncBrandPreview);

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
            syncBrandPreview();
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
                syncBrandPreview();
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
        })();
    </script>
    <script src="../static/js/admin-shell.js"></script>
</body>
</html>
