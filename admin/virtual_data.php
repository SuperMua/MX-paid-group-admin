<?php
require_once 'login_check.php';
require_once 'virtual_data_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'toggle') {
        $enabled = isset($_POST['enabled']) && (string) $_POST['enabled'] === '1';
        vd_set_enabled($enabled);
        echo json_encode(array(
            'success' => true,
            'message' => $enabled ? '已开启虚拟数据模式' : '已关闭虚拟数据模式',
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'regenerate') {
        vd_regenerate_dataset();
        vd_set_enabled(true);
        echo json_encode(array(
            'success' => true,
            'message' => '虚拟数据已重新生成',
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'clear') {
        vd_boot_session();
        $_SESSION['virtual_data']['enabled'] = false;
        $_SESSION['virtual_data']['dataset'] = null;
        $_SESSION['virtual_data']['seed'] = null;
        $_SESSION['virtual_data']['generated_at'] = null;
        $_SESSION['virtual_data']['updated_at'] = vd_now_string();
        echo json_encode(array(
            'success' => true,
            'message' => '虚拟数据已清空并关闭',
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'get_dataset_json') {
        echo json_encode(array(
            'success' => true,
            'message' => '读取成功',
            'dataset_json' => vd_get_dataset_json_pretty(),
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($action === 'get_template_json') {
        echo json_encode(array(
            'success' => true,
            'message' => '模板已载入',
            'dataset_json' => vd_get_template_json_pretty(),
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($action === 'save_dataset_json') {
        $jsonText = isset($_POST['dataset_json']) ? (string) $_POST['dataset_json'] : '';
        $message = '';
        if (!vd_save_dataset_from_json($jsonText, $message)) {
            echo json_encode(array(
                'success' => false,
                'message' => $message
            ), JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(array(
            'success' => true,
            'message' => $message,
            'dataset_json' => vd_get_dataset_json_pretty(),
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode(array('success' => false, 'message' => '未知操作'), JSON_UNESCAPED_UNICODE);
    exit;
}

$state = vd_get_state_payload();
$snapshot = $state['snapshot'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/admin.css">
    <title>虚拟数据工具</title>
    <style>
        .virtual-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .virtual-metric {
            border: 1px solid rgba(83, 86, 251, 0.2);
            border-radius: 16px;
            background: #f9faff;
            padding: 12px;
        }

        .virtual-metric .label {
            color: #667085;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .virtual-metric .value {
            color: #1f2a47;
            font-size: 20px;
            font-weight: 700;
        }

        .virtual-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .virtual-meta {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(83, 86, 251, 0.18);
            background: #f5f7ff;
            color: #4f5b88;
            font-size: 13px;
            line-height: 1.7;
        }

        .virtual-state-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .virtual-state-pill.on {
            color: #0f8f35;
            background: #f6ffed;
            border-color: #b7eb8f;
        }

        .virtual-state-pill.off {
            color: #b54708;
            background: #fff7ed;
            border-color: #fed7aa;
        }

        .virtual-note {
            margin-top: 12px;
            font-size: 13px;
            color: #667085;
            line-height: 1.7;
        }

        .virtual-editor-wrap {
            margin-top: 18px;
            border: 1px solid rgba(83, 86, 251, 0.2);
            border-radius: 16px;
            background: #f9faff;
            padding: 12px;
        }

        .virtual-editor-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .virtual-editor-title {
            margin: 0;
            font-size: 14px;
            color: #2d3c67;
            font-weight: 700;
        }

        .virtual-editor-tip {
            margin: 0;
            font-size: 12px;
            color: #667085;
        }

        .virtual-editor-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .virtual-json-editor {
            width: 100%;
            min-height: 320px;
            padding: 12px;
            border-radius: 14px;
            border: 1px solid rgba(83, 86, 251, 0.26);
            background: #101827;
            color: #ecf2ff;
            font-size: 12px;
            line-height: 1.65;
            font-family: Consolas, Monaco, 'Courier New', monospace;
            resize: vertical;
            box-sizing: border-box;
        }

        .virtual-json-editor:focus {
            outline: none;
            border-color: rgba(83, 86, 251, 0.8);
            box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.2);
        }

        @media (max-width: 991px) {
            .virtual-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .virtual-json-editor {
                min-height: 260px;
            }
        }
    </style>
</head>
<body class="settings-pro-body">
<div class="navbar">
    <a class="back-button left-arrow" href="upload_cache.php" onclick="if(history.length>1){history.back();return false;}if(document.referrer){location.href=document.referrer;return false;}"></a>
    <div class="title">虚拟数据工具</div>
</div>
<main class="settings-pro-shell" id="virtualDataPage">
    <header class="settings-pro-header">
        <h2 class="settings-pro-title">演示数据控制台</h2>
        <p class="settings-pro-subtitle">用于演示后台效果：可一键启用虚拟数据，覆盖控制台、订单、访客、审核等核心统计展示。</p>
        <span id="statePill" class="virtual-state-pill <?php echo $state['enabled'] ? 'on' : 'off'; ?>">
            <?php echo $state['enabled'] ? '虚拟数据：已开启' : '虚拟数据：已关闭'; ?>
        </span>
    </header>

    <section class="settings-pro-card">
        <h3>当前虚拟快照</h3>
        <div class="virtual-grid">
            <div class="virtual-metric"><div class="label">总收入</div><div class="value" id="mTotalIncome"><?php echo htmlspecialchars($snapshot['total_income']); ?></div></div>
            <div class="virtual-metric"><div class="label">今日收入</div><div class="value" id="mTodayIncome"><?php echo htmlspecialchars($snapshot['today_income']); ?></div></div>
            <div class="virtual-metric"><div class="label">今日订单</div><div class="value" id="mTodayOrders"><?php echo htmlspecialchars((string)$snapshot['today_orders']); ?></div></div>
            <div class="virtual-metric"><div class="label">昨日访客</div><div class="value" id="mYesterdayVisitors"><?php echo htmlspecialchars((string)$snapshot['yesterday_visitors']); ?></div></div>
            <div class="virtual-metric"><div class="label">今日访客</div><div class="value" id="mTodayVisitors"><?php echo htmlspecialchars((string)$snapshot['today_visitors']); ?></div></div>
            <div class="virtual-metric"><div class="label">待审核IP</div><div class="value" id="mPending"><?php echo htmlspecialchars((string)$snapshot['unreviewed_count']); ?></div></div>
        </div>

        <div class="virtual-actions">
            <button class="admin-pill-btn admin-pill-btn-primary" type="button" id="toggleBtn"><?php echo $state['enabled'] ? '关闭虚拟数据' : '开启虚拟数据'; ?></button>
            <button class="admin-pill-btn admin-pill-btn-light" type="button" id="regenerateBtn">重新生成样本</button>
            <button class="admin-pill-btn admin-pill-btn-danger" type="button" id="clearBtn">清空并关闭</button>
        </div>

        <div class="virtual-meta" id="metaText">
            数据种子：<?php echo $state['seed'] ? htmlspecialchars((string)$state['seed']) : '--'; ?><br>
            生成时间：<?php echo $state['generated_at'] ? htmlspecialchars($state['generated_at']) : '--'; ?><br>
            更新时间：<?php echo $state['updated_at'] ? htmlspecialchars($state['updated_at']) : '--'; ?>
        </div>

        <p class="virtual-note">说明：开启后仅影响后台展示数据，不修改真实业务表。你可在演示结束后点击“清空并关闭”恢复默认真实数据模式。</p>

        <div class="virtual-editor-wrap">
            <div class="virtual-editor-head">
                <div>
                    <p class="virtual-editor-title">全量自定义数据（JSON）</p>
                    <p class="virtual-editor-tip">支持自定义 `orders / visitors / reviews` 全字段，保存后自动开启虚拟模式。</p>
                </div>
                <div class="virtual-editor-actions">
                    <button class="admin-pill-btn admin-pill-btn-light" type="button" id="loadCurrentBtn">读取当前数据</button>
                    <button class="admin-pill-btn admin-pill-btn-light" type="button" id="loadTemplateBtn">填入模板</button>
                    <button class="admin-pill-btn admin-pill-btn-light" type="button" id="formatJsonBtn">格式化JSON</button>
                    <button class="admin-pill-btn admin-pill-btn-primary" type="button" id="saveJsonBtn">保存自定义</button>
                </div>
            </div>
            <textarea id="datasetEditor" class="virtual-json-editor" spellcheck="false" placeholder='{\n  "orders": [],\n  "visitors": [],\n  "reviews": []\n}'></textarea>
        </div>
    </section>
</main>
<div class="settings-pro-toast" id="vdToast">操作成功</div>
<script>
(function () {
    const root = document.getElementById('virtualDataPage');
    if (!root || root.dataset.boundVirtualDataPage === '1') {
        return;
    }
    root.dataset.boundVirtualDataPage = '1';

    const statePill = document.getElementById('statePill');
    const metaText = document.getElementById('metaText');
    const toggleBtn = document.getElementById('toggleBtn');
    const regenerateBtn = document.getElementById('regenerateBtn');
    const clearBtn = document.getElementById('clearBtn');
    const loadCurrentBtn = document.getElementById('loadCurrentBtn');
    const loadTemplateBtn = document.getElementById('loadTemplateBtn');
    const saveJsonBtn = document.getElementById('saveJsonBtn');
    const formatJsonBtn = document.getElementById('formatJsonBtn');
    const datasetEditor = document.getElementById('datasetEditor');
    const toast = document.getElementById('vdToast');

    function showToast(message, isError) {
        if (!toast) {
            return;
        }
        toast.textContent = message;
        toast.style.background = isError ? 'rgba(185,28,28,0.9)' : 'rgba(38,44,75,0.88)';
        toast.style.display = 'block';
        setTimeout(function () {
            toast.style.display = 'none';
        }, 1800);
    }

    function updateState(state) {
        const enabled = !!(state && state.enabled);
        const snapshot = state && state.snapshot ? state.snapshot : {};

        statePill.textContent = enabled ? '虚拟数据：已开启' : '虚拟数据：已关闭';
        statePill.classList.toggle('on', enabled);
        statePill.classList.toggle('off', !enabled);
        toggleBtn.textContent = enabled ? '关闭虚拟数据' : '开启虚拟数据';

        document.getElementById('mTotalIncome').textContent = snapshot.total_income || '0.00';
        document.getElementById('mTodayIncome').textContent = snapshot.today_income || '0.00';
        document.getElementById('mTodayOrders').textContent = String(snapshot.today_orders || 0);
        document.getElementById('mYesterdayVisitors').textContent = String(snapshot.yesterday_visitors || 0);
        document.getElementById('mTodayVisitors').textContent = String(snapshot.today_visitors || 0);
        document.getElementById('mPending').textContent = String(snapshot.unreviewed_count || 0);

        const seedText = state.seed ? String(state.seed) : '--';
        const generatedAt = state.generated_at || '--';
        const updatedAt = state.updated_at || '--';
        metaText.innerHTML = '数据种子：' + seedText + '<br>生成时间：' + generatedAt + '<br>更新时间：' + updatedAt;
    }

    function postAction(action, extra) {
        const params = new URLSearchParams();
        params.set('action', action);
        if (extra) {
            Object.keys(extra).forEach(function (key) {
                params.set(key, extra[key]);
            });
        }

        return fetch('virtual_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        }).then(function (res) {
            return res.json();
        });
    }

    toggleBtn.addEventListener('click', function () {
        const enable = toggleBtn.textContent.indexOf('开启') !== -1;
        postAction('toggle', { enabled: enable ? '1' : '0' })
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '操作失败', true);
                    return;
                }
                updateState(resp.state || {});
                showToast(resp.message || '操作成功', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    regenerateBtn.addEventListener('click', function () {
        postAction('regenerate')
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '操作失败', true);
                    return;
                }
                updateState(resp.state || {});
                showToast(resp.message || '操作成功', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    clearBtn.addEventListener('click', function () {
        if (!confirm('确认清空虚拟数据并关闭演示模式吗？')) {
            return;
        }
        postAction('clear')
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '操作失败', true);
                    return;
                }
                updateState(resp.state || {});
                showToast(resp.message || '操作成功', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    loadCurrentBtn.addEventListener('click', function () {
        postAction('get_dataset_json')
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '读取失败', true);
                    return;
                }
                datasetEditor.value = resp.dataset_json || '';
                updateState(resp.state || {});
                showToast(resp.message || '已读取', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    loadTemplateBtn.addEventListener('click', function () {
        postAction('get_template_json')
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '读取失败', true);
                    return;
                }
                datasetEditor.value = resp.dataset_json || '';
                showToast(resp.message || '模板已载入', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    formatJsonBtn.addEventListener('click', function () {
        const raw = (datasetEditor.value || '').trim();
        if (!raw) {
            showToast('请先输入 JSON 内容', true);
            return;
        }
        try {
            const obj = JSON.parse(raw);
            datasetEditor.value = JSON.stringify(obj, null, 2);
            showToast('JSON 已格式化', false);
        } catch (err) {
            showToast('JSON 格式错误：' + err.message, true);
        }
    });

    saveJsonBtn.addEventListener('click', function () {
        const raw = (datasetEditor.value || '').trim();
        if (!raw) {
            showToast('请先输入 JSON 内容', true);
            return;
        }
        postAction('save_dataset_json', { dataset_json: raw })
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '保存失败', true);
                    return;
                }
                datasetEditor.value = resp.dataset_json || raw;
                updateState(resp.state || {});
                showToast(resp.message || '保存成功', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    postAction('get_dataset_json')
        .then(function (resp) {
            if (!resp.success) {
                return;
            }
            datasetEditor.value = resp.dataset_json || '';
        })
        .catch(function () {
            // 忽略初始化失败
        });
})();
</script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>
