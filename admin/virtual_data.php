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

    if ($action === 'generate_with_params') {
        $options = array(
            'preset' => isset($_POST['preset']) ? trim((string) $_POST['preset']) : 'balanced',
            'order_count' => $_POST['order_count'] ?? '',
            'visitor_count' => $_POST['visitor_count'] ?? '',
            'review_group_count' => $_POST['review_group_count'] ?? '',
            'review_images_min' => $_POST['review_images_min'] ?? '',
            'review_images_max' => $_POST['review_images_max'] ?? '',
            'paid_ratio' => $_POST['paid_ratio'] ?? '',
            'pending_ratio' => $_POST['pending_ratio'] ?? '',
            'approved_ratio' => $_POST['approved_ratio'] ?? '',
            'rejected_ratio' => $_POST['rejected_ratio'] ?? '',
            'today_order_ratio' => $_POST['today_order_ratio'] ?? '',
            'payment_method_wx_ratio' => $_POST['payment_method_wx_ratio'] ?? '',
            'order_days_range' => $_POST['order_days_range'] ?? '',
            'visitor_days_range' => $_POST['visitor_days_range'] ?? '',
            'review_days_range' => $_POST['review_days_range'] ?? '',
            'order_names' => $_POST['order_names'] ?? '',
            'locations' => $_POST['locations'] ?? '',
            'user_agents' => $_POST['user_agents'] ?? '',
            'pages' => $_POST['pages'] ?? '',
            'price_list' => $_POST['price_list'] ?? '',
            'reviewer_names' => $_POST['reviewer_names'] ?? ''
        );
        vd_regenerate_dataset($options);
        vd_set_enabled(true);
        echo json_encode(array(
            'success' => true,
            'message' => '已按参数生成虚拟数据',
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'apply_snapshot_only') {
        $snapshot = array(
            'total_income' => $_POST['total_income'] ?? '',
            'today_income' => $_POST['today_income'] ?? '',
            'today_orders' => $_POST['today_orders'] ?? '',
            'yesterday_visitors' => $_POST['yesterday_visitors'] ?? '',
            'today_visitors' => $_POST['today_visitors'] ?? '',
            'unreviewed_count' => $_POST['unreviewed_count'] ?? ''
        );
        vd_set_snapshot_override($snapshot);
        vd_boot_session();
        $_SESSION['virtual_data']['enabled'] = true;
        $_SESSION['virtual_data']['updated_at'] = vd_now_string();
        echo json_encode(array(
            'success' => true,
            'message' => '已应用核心快照虚拟数据',
            'state' => vd_get_state_payload()
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'get_preset_options') {
        $preset = isset($_POST['preset']) ? trim((string) $_POST['preset']) : 'balanced';
        echo json_encode(array(
            'success' => true,
            'message' => '预设参数已加载',
            'options' => vd_get_generator_preset_options($preset)
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'clear') {
        vd_boot_session();
        $_SESSION['virtual_data']['enabled'] = false;
        $_SESSION['virtual_data']['dataset'] = null;
        $_SESSION['virtual_data']['seed'] = null;
        $_SESSION['virtual_data']['generated_at'] = null;
        $_SESSION['virtual_data']['snapshot_override'] = null;
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
$defaultOptions = vd_default_generator_options();
$modeText = !empty($state['snapshot_override_active']) ? '核心快照模式（仅6项）' : '全量演示模式';
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

        .generator-wrap {
            margin-top: 18px;
            border: 1px solid rgba(83, 86, 251, 0.2);
            border-radius: 16px;
            background: #f9faff;
            padding: 12px;
        }

        .generator-wrap h4 {
            margin: 0 0 8px;
            font-size: 14px;
            color: #2d3c67;
            font-weight: 700;
        }

        .generator-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 8px;
        }

        .generator-field {
            flex: 1 1 220px;
            min-width: 180px;
        }

        .generator-field label {
            display: block;
            font-size: 12px;
            color: #4f5b88;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .generator-field input,
        .generator-field select,
        .generator-field textarea {
            width: 100%;
            border: 1px solid rgba(83, 86, 251, 0.26);
            border-radius: 12px;
            padding: 9px 10px;
            font-size: 13px;
            background: #fff;
            box-sizing: border-box;
        }

        .generator-field textarea {
            min-height: 84px;
            resize: vertical;
        }

        .generator-field input:focus,
        .generator-field select:focus,
        .generator-field textarea:focus {
            outline: none;
            border-color: rgba(83, 86, 251, 0.8);
            box-shadow: 0 0 0 3px rgba(83, 86, 251, 0.2);
        }

        .generator-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        @media (max-width: 991px) {
            .virtual-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
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
            数据模式：<?php echo htmlspecialchars($modeText); ?><br>
            数据种子：<?php echo $state['seed'] ? htmlspecialchars((string)$state['seed']) : '--'; ?><br>
            生成时间：<?php echo $state['generated_at'] ? htmlspecialchars($state['generated_at']) : '--'; ?><br>
            更新时间：<?php echo $state['updated_at'] ? htmlspecialchars($state['updated_at']) : '--'; ?>
        </div>

        <p class="virtual-note">说明：开启后仅影响后台展示数据，不修改真实业务表。你可在演示结束后点击“清空并关闭”恢复默认真实数据模式。</p>

        <div class="generator-wrap">
            <h4>方案A：预设一键生成（可调参数）</h4>
            <div class="generator-grid">
                <div class="generator-field">
                    <label for="presetSelect">预设模板</label>
                    <select id="presetSelect">
                        <option value="balanced">平衡运营模板（默认）</option>
                        <option value="high_conversion">高转化模板</option>
                        <option value="traffic_burst">高流量模板</option>
                        <option value="strict_audit">严格审核模板</option>
                        <option value="cold_start">冷启动模板</option>
                    </select>
                </div>
                <div class="generator-field">
                    <label for="orderCount">订单总量（order_count）</label>
                    <input id="orderCount" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['order_count']; ?>">
                </div>
                <div class="generator-field">
                    <label for="visitorCount">访客总量（visitor_count）</label>
                    <input id="visitorCount" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['visitor_count']; ?>">
                </div>
                <div class="generator-field">
                    <label for="reviewGroupCount">审核IP组数（review_group_count）</label>
                    <input id="reviewGroupCount" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['review_group_count']; ?>">
                </div>
                <div class="generator-field">
                    <label for="paidRatio">已支付比例(%)（paid_ratio）</label>
                    <input id="paidRatio" type="number" min="0" max="100" step="1" value="<?php echo (int) $defaultOptions['paid_ratio']; ?>">
                </div>
                <div class="generator-field">
                    <label for="todayOrderRatio">今日订单占比(%)（today_order_ratio）</label>
                    <input id="todayOrderRatio" type="number" min="0" max="100" step="1" value="<?php echo (int) $defaultOptions['today_order_ratio']; ?>">
                </div>
                <div class="generator-field">
                    <label for="pendingRatio">待审核比例(%)（pending_ratio）</label>
                    <input id="pendingRatio" type="number" min="0" step="1" value="<?php echo (int) $defaultOptions['pending_ratio']; ?>">
                </div>
                <div class="generator-field">
                    <label for="approvedRatio">已通过比例(%)（approved_ratio）</label>
                    <input id="approvedRatio" type="number" min="0" step="1" value="<?php echo (int) $defaultOptions['approved_ratio']; ?>">
                </div>
                <div class="generator-field">
                    <label for="rejectedRatio">不通过比例(%)（rejected_ratio）</label>
                    <input id="rejectedRatio" type="number" min="0" step="1" value="<?php echo (int) $defaultOptions['rejected_ratio']; ?>">
                </div>
            </div>
            <div class="generator-actions">
                <button class="admin-pill-btn admin-pill-btn-light" type="button" id="applyPresetBtn">应用预设参数</button>
                <button class="admin-pill-btn admin-pill-btn-primary" type="button" id="generatePresetBtn">按当前参数一键生成</button>
            </div>
        </div>

        <div class="generator-wrap">
            <h4>方案B：手动填写参数（数组输入）</h4>
            <div class="generator-grid">
                <div class="generator-field">
                    <label for="orderNames">订单名称数组（order_names，逗号分隔）</label>
                    <textarea id="orderNames"><?php echo htmlspecialchars($defaultOptions['order_names']); ?></textarea>
                </div>
                <div class="generator-field">
                    <label for="priceList">金额数组（price_list，逗号分隔）</label>
                    <textarea id="priceList"><?php echo htmlspecialchars($defaultOptions['price_list']); ?></textarea>
                </div>
                <div class="generator-field">
                    <label for="locations">地区数组（locations，逗号分隔）</label>
                    <textarea id="locations"><?php echo htmlspecialchars($defaultOptions['locations']); ?></textarea>
                </div>
                <div class="generator-field">
                    <label for="userAgents">设备数组（user_agents，逗号分隔）</label>
                    <textarea id="userAgents"><?php echo htmlspecialchars($defaultOptions['user_agents']); ?></textarea>
                </div>
                <div class="generator-field">
                    <label for="pages">访问页面数组（pages，逗号分隔）</label>
                    <textarea id="pages"><?php echo htmlspecialchars($defaultOptions['pages']); ?></textarea>
                </div>
                <div class="generator-field">
                    <label for="reviewerNames">审核员数组（reviewer_names，逗号分隔）</label>
                    <textarea id="reviewerNames"><?php echo htmlspecialchars($defaultOptions['reviewer_names']); ?></textarea>
                </div>
                <div class="generator-field">
                    <label for="reviewImagesMin">每组最少图片数（review_images_min）</label>
                    <input id="reviewImagesMin" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['review_images_min']; ?>">
                </div>
                <div class="generator-field">
                    <label for="reviewImagesMax">每组最多图片数（review_images_max）</label>
                    <input id="reviewImagesMax" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['review_images_max']; ?>">
                </div>
                <div class="generator-field">
                    <label for="wxRatio">微信支付占比(%)（payment_method_wx_ratio）</label>
                    <input id="wxRatio" type="number" min="0" max="100" step="1" value="<?php echo (int) $defaultOptions['payment_method_wx_ratio']; ?>">
                </div>
                <div class="generator-field">
                    <label for="orderDaysRange">订单回溯天数（order_days_range）</label>
                    <input id="orderDaysRange" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['order_days_range']; ?>">
                </div>
                <div class="generator-field">
                    <label for="visitorDaysRange">访客回溯天数（visitor_days_range）</label>
                    <input id="visitorDaysRange" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['visitor_days_range']; ?>">
                </div>
                <div class="generator-field">
                    <label for="reviewDaysRange">审核回溯天数（review_days_range）</label>
                    <input id="reviewDaysRange" type="number" min="1" step="1" value="<?php echo (int) $defaultOptions['review_days_range']; ?>">
                </div>
            </div>
            <div class="generator-actions">
                <button class="admin-pill-btn admin-pill-btn-light" type="button" id="resetManualBtn">恢复默认参数</button>
                <button class="admin-pill-btn admin-pill-btn-primary" type="button" id="generateManualBtn">按手动参数生成</button>
            </div>
        </div>

        <div class="generator-wrap">
            <h4>方案C：仅虚拟核心快照（总览6项）</h4>
            <div class="generator-grid">
                <div class="generator-field">
                    <label for="snapshotTotalIncome">总收入（total_income）</label>
                    <input id="snapshotTotalIncome" type="number" min="0" step="0.01" value="4508.40">
                </div>
                <div class="generator-field">
                    <label for="snapshotTodayIncome">今日收入（today_income）</label>
                    <input id="snapshotTodayIncome" type="number" min="0" step="0.01" value="906.80">
                </div>
                <div class="generator-field">
                    <label for="snapshotTodayOrders">今日订单（today_orders）</label>
                    <input id="snapshotTodayOrders" type="number" min="0" step="1" value="23">
                </div>
                <div class="generator-field">
                    <label for="snapshotYesterdayVisitors">昨日访客（yesterday_visitors）</label>
                    <input id="snapshotYesterdayVisitors" type="number" min="0" step="1" value="22">
                </div>
                <div class="generator-field">
                    <label for="snapshotTodayVisitors">今日访客（today_visitors）</label>
                    <input id="snapshotTodayVisitors" type="number" min="0" step="1" value="24">
                </div>
                <div class="generator-field">
                    <label for="snapshotPending">待审核IP（unreviewed_count）</label>
                    <input id="snapshotPending" type="number" min="0" step="1" value="14">
                </div>
            </div>
            <div class="generator-actions">
                <button class="admin-pill-btn admin-pill-btn-light" type="button" id="resetSnapshotBtn">恢复示例值</button>
                <button class="admin-pill-btn admin-pill-btn-primary" type="button" id="generateSnapshotBtn">仅应用这6项数据</button>
            </div>
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
    const presetSelect = document.getElementById('presetSelect');
    const applyPresetBtn = document.getElementById('applyPresetBtn');
    const generatePresetBtn = document.getElementById('generatePresetBtn');
    const resetManualBtn = document.getElementById('resetManualBtn');
    const generateManualBtn = document.getElementById('generateManualBtn');
    const resetSnapshotBtn = document.getElementById('resetSnapshotBtn');
    const generateSnapshotBtn = document.getElementById('generateSnapshotBtn');
    const toast = document.getElementById('vdToast');
    const defaultOptions = <?php echo json_encode($defaultOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const snapshotDefaults = {
        total_income: '4508.40',
        today_income: '906.80',
        today_orders: '23',
        yesterday_visitors: '22',
        today_visitors: '24',
        unreviewed_count: '14'
    };

    const fieldMap = {
        orderCount: 'order_count',
        visitorCount: 'visitor_count',
        reviewGroupCount: 'review_group_count',
        reviewImagesMin: 'review_images_min',
        reviewImagesMax: 'review_images_max',
        paidRatio: 'paid_ratio',
        pendingRatio: 'pending_ratio',
        approvedRatio: 'approved_ratio',
        rejectedRatio: 'rejected_ratio',
        todayOrderRatio: 'today_order_ratio',
        wxRatio: 'payment_method_wx_ratio',
        orderDaysRange: 'order_days_range',
        visitorDaysRange: 'visitor_days_range',
        reviewDaysRange: 'review_days_range',
        orderNames: 'order_names',
        locations: 'locations',
        userAgents: 'user_agents',
        pages: 'pages',
        priceList: 'price_list',
        reviewerNames: 'reviewer_names'
    };

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
        const modeText = state.snapshot_override_active ? '核心快照模式（仅6项）' : '全量演示模式';
        metaText.innerHTML = '数据模式：' + modeText + '<br>数据种子：' + seedText + '<br>生成时间：' + generatedAt + '<br>更新时间：' + updatedAt;
    }

    function setFieldValues(options) {
        if (!options) {
            return;
        }
        Object.keys(fieldMap).forEach(function (domId) {
            const optionKey = fieldMap[domId];
            const el = document.getElementById(domId);
            if (!el || typeof options[optionKey] === 'undefined' || options[optionKey] === null) {
                return;
            }
            el.value = String(options[optionKey]);
        });
    }

    function collectParams() {
        const payload = {
            preset: presetSelect ? presetSelect.value : 'balanced'
        };
        Object.keys(fieldMap).forEach(function (domId) {
            const optionKey = fieldMap[domId];
            const el = document.getElementById(domId);
            if (!el) {
                return;
            }
            payload[optionKey] = String(el.value || '').trim();
        });
        return payload;
    }

    function setSnapshotFields(values) {
        const data = values || snapshotDefaults;
        const totalIncomeInput = document.getElementById('snapshotTotalIncome');
        const todayIncomeInput = document.getElementById('snapshotTodayIncome');
        const todayOrdersInput = document.getElementById('snapshotTodayOrders');
        const yesterdayVisitorsInput = document.getElementById('snapshotYesterdayVisitors');
        const todayVisitorsInput = document.getElementById('snapshotTodayVisitors');
        const pendingInput = document.getElementById('snapshotPending');

        if (totalIncomeInput) {
            totalIncomeInput.value = String(data.total_income || '0.00');
        }
        if (todayIncomeInput) {
            todayIncomeInput.value = String(data.today_income || '0.00');
        }
        if (todayOrdersInput) {
            todayOrdersInput.value = String(data.today_orders || '0');
        }
        if (yesterdayVisitorsInput) {
            yesterdayVisitorsInput.value = String(data.yesterday_visitors || '0');
        }
        if (todayVisitorsInput) {
            todayVisitorsInput.value = String(data.today_visitors || '0');
        }
        if (pendingInput) {
            pendingInput.value = String(data.unreviewed_count || '0');
        }
    }

    function collectSnapshotParams() {
        const totalIncomeInput = document.getElementById('snapshotTotalIncome');
        const todayIncomeInput = document.getElementById('snapshotTodayIncome');
        const todayOrdersInput = document.getElementById('snapshotTodayOrders');
        const yesterdayVisitorsInput = document.getElementById('snapshotYesterdayVisitors');
        const todayVisitorsInput = document.getElementById('snapshotTodayVisitors');
        const pendingInput = document.getElementById('snapshotPending');

        return {
            total_income: totalIncomeInput ? String(totalIncomeInput.value || '').trim() : '0',
            today_income: todayIncomeInput ? String(todayIncomeInput.value || '').trim() : '0',
            today_orders: todayOrdersInput ? String(todayOrdersInput.value || '').trim() : '0',
            yesterday_visitors: yesterdayVisitorsInput ? String(yesterdayVisitorsInput.value || '').trim() : '0',
            today_visitors: todayVisitorsInput ? String(todayVisitorsInput.value || '').trim() : '0',
            unreviewed_count: pendingInput ? String(pendingInput.value || '').trim() : '0'
        };
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

    applyPresetBtn.addEventListener('click', function () {
        const preset = presetSelect ? presetSelect.value : 'balanced';
        postAction('get_preset_options', { preset: preset })
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '加载预设失败', true);
                    return;
                }
                setFieldValues(resp.options || {});
                showToast(resp.message || '预设参数已加载', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    generatePresetBtn.addEventListener('click', function () {
        const params = collectParams();
        postAction('generate_with_params', params)
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '生成失败', true);
                    return;
                }
                updateState(resp.state || {});
                showToast(resp.message || '已生成虚拟数据', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    resetManualBtn.addEventListener('click', function () {
        if (presetSelect) {
            presetSelect.value = 'balanced';
        }
        setFieldValues(defaultOptions);
        showToast('已恢复默认参数', false);
    });

    generateManualBtn.addEventListener('click', function () {
        const params = collectParams();
        postAction('generate_with_params', params)
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '生成失败', true);
                    return;
                }
                updateState(resp.state || {});
                showToast(resp.message || '手动参数已生成', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    resetSnapshotBtn.addEventListener('click', function () {
        setSnapshotFields(snapshotDefaults);
        showToast('已恢复快照示例值', false);
    });

    generateSnapshotBtn.addEventListener('click', function () {
        const params = collectSnapshotParams();
        postAction('apply_snapshot_only', params)
            .then(function (resp) {
                if (!resp.success) {
                    showToast(resp.message || '应用失败', true);
                    return;
                }
                updateState(resp.state || {});
                showToast(resp.message || '已应用核心快照', false);
            })
            .catch(function () {
                showToast('请求失败，请稍后重试', true);
            });
    });

    if (presetSelect) {
        presetSelect.addEventListener('change', function () {
            postAction('get_preset_options', { preset: presetSelect.value })
                .then(function (resp) {
                    if (!resp.success) {
                        return;
                    }
                    setFieldValues(resp.options || {});
                })
                .catch(function () {
                    // 忽略自动加载失败
                });
        });
    }

    if (presetSelect && !presetSelect.value) {
        presetSelect.value = 'balanced';
    }
    setFieldValues(defaultOptions);
    setSnapshotFields(snapshotDefaults);
    if (presetSelect) {
        postAction('get_preset_options', { preset: presetSelect.value || 'balanced' })
            .then(function (resp) {
                if (!resp.success) {
                    return;
                }
                setFieldValues(resp.options || {});
                showToast(resp.message || '模板已载入', false);
            })
            .catch(function () {
                // 忽略初始化失败
            });
    }
})();
</script>
<script src="../static/js/admin-shell.js"></script>
</body>
</html>
