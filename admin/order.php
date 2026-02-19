<?php
require_once 'order-backend.php';
require_once 'login_check.php';  
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/css/admin.css">
    <title>订单详情</title>
    <style>
    </style>
</head>
<body>
    <div class="navbar">
        <a class="back-button left-arrow" href="index.php"></a>
        <div class="title">订单详情</div>
    </div>
    <div class="statistics order-djsd">
        <p>总订单：<span class="order-dingdan"><?php echo $countRow['total_orders']; ?></span></p>
        <p>已支付：<span class="order-dingdan"><?php echo $countRow['paid_orders']; ?></span></p>
        <p>未支付：<span class="order-dingdan"><?php echo $countRow['unpaid_orders']; ?></span></p>
    </div>
    <div class="admin-action-bar">
        <a class="admin-pill-btn admin-pill-btn-primary" href="order_export.php">导出 Excel</a>
        <button class="admin-pill-btn admin-pill-btn-danger" type="button" onclick="showConfirmModal('delete_all')">清空记录</button>
    </div>

    <!-- 内容容器 -->
    <div class="content-container order-content-grid">
        <div class="tab-content order-list-grid">
            <?php if (empty($orders)): ?>
                <p class="no-data">没有记录</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order_kls">
                        <div class="order-hrsk">
                            <span class="info-conpih">订单号：<?php echo $order['order_number']; ?></span>
                            <button class="admin-pill-btn admin-pill-btn-light" type="button" onclick='showConfirmModal("delete_single", <?php echo intval($order['id']); ?>, <?php echo json_encode((string)$order['order_number'], JSON_UNESCAPED_UNICODE); ?>)'>删除</button>
                        </div>
                        <div class="order-item_yup">
                            <div class="order-info">
                                <div class="info-name">名称：<span class="info-content"><?php echo $order['name']; ?></span></div>
                                <div class="info-name">地区：<span class="info-content"><?php echo $order['ip_location']; ?></span></div>
                                <div class="info-name">时间：<span class="info-content"><?php echo $order['payment_time']; ?></span></div>
                            </div>
                            <div class="amount-payment">
                                <?php if ($order['payment_status'] === '已支付'): ?>
                                    <div class="amount">+<?php echo $order['money']; ?></div>
                                <?php else: ?>
                                    <div class="amount opiu"><?php echo $order['money']; ?></div>
                                <?php endif; ?>
                                <div class="info-conuie <?php echo ($order['payment_status'] === '未支付') ? 'opiu' : ''; ?>">
                                    <?php echo $paymentMethodMap[$order['payment_method']]?? '未知支付方式'; ?>收款
                                </div>
								<div class="amount opiuyr">
								    <?php if ($order['payment_status'] === '已支付'): ?>
								    <span class="info-conuie">已支付</span>
								    <?php else: ?>
								        <span class="info-conuie opiu">未支付</span>
								    <?php endif; ?>
								</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($countRow['total_orders'] > $perPage): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">上一页</a>
                <?php endif; ?>
                <?php if (($page * $perPage) < $countRow['total_orders']): ?>
                    <a href="?page=<?php echo $page + 1; ?>">下一页</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<!-- 确认弹窗 -->
    <div id="confirmModal" class="custom-modal">
        <div class="modal-content">
            <p id="confirmMessage"></p>
            <button class="modal-button cancel" onclick="closeConfirmModal()">取消</button>
			<button class="modal-button confirm" onclick="confirmAction()">二次确认删除</button>
        </div>
    </div>

    <!-- 操作成功弹窗 -->
    <div id="successModal" class="custom-modal">
        <div class="modal-content2">
            <img src="../result/images/ytg.png" alt="">
            <p>操作成功</p>
        </div>
    </div>

    <script>
            var isActionProcessing = false;

            // 显示确认弹窗
            function showConfirmModal(actionType, orderId, orderNumber) {
                if (isActionProcessing) {
                    return;
                }

                document.getElementById("confirmModal").style.display = "flex";
                document.getElementById("confirmModal").style.justifyContent = "center";
                document.getElementById("confirmModal").style.alignItems = "center";
            
                const confirmMessage = document.getElementById("confirmMessage");
                const confirmButton = document.querySelector("#confirmModal .confirm");
                if (actionType === 'delete_single') {
                    confirmMessage.textContent = `是否删除订单：${orderNumber}（删除后不可恢复）`;
                } else if (actionType === 'delete_all') {
                    confirmMessage.textContent = "是否清空全部订单记录？（删除后不可恢复）";
                }
                confirmButton.disabled = false;
                confirmButton.textContent = "二次确认删除";
            
                // 存储操作类型、订单ID和订单编号
                document.getElementById("confirmModal").setAttribute("data-action", actionType);
                document.getElementById("confirmModal").setAttribute("data-order-id", orderId);
                document.getElementById("confirmModal").setAttribute("data-order-number", orderNumber);
            }
    
            // 关闭确认弹窗
            function closeConfirmModal() {
                document.getElementById("confirmModal").style.display = "none";
            }
    
            // 执行确认操作
            function confirmAction() {
                if (isActionProcessing) {
                    return;
                }

                const actionType = document.getElementById("confirmModal").getAttribute("data-action");
                const orderId = document.getElementById("confirmModal").getAttribute("data-order-id");
                const confirmButton = document.querySelector("#confirmModal .confirm");
                closeConfirmModal();
    
                let requestBody;
                if (actionType === 'delete_single') {
                    requestBody = `action=delete_single&order_id=${encodeURIComponent(orderId)}`;
                } else if (actionType === 'delete_all') {
                    requestBody = "action=delete_all";
                } else {
                    alert("未知操作类型，请刷新后重试");
                    return;
                }

                isActionProcessing = true;
                confirmButton.disabled = true;
                confirmButton.textContent = "处理中...";
    
                fetch("order-backend.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: requestBody
                })
                .then(response => response.text())
                .then(data => {
                    if (data === "操作成功") {
                        document.getElementById("successModal").style.display = "flex";
                        document.getElementById("successModal").style.justifyContent = "center";
                        document.getElementById("successModal").style.alignItems = "center";
                        setTimeout(() => {
                            document.getElementById("successModal").style.display = "none";
                            location.reload(); // 刷新页面
                        }, 2000); // 2秒后关闭弹窗并刷新页面
                    } else {
                        alert("操作失败: " + data);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("网络异常，请稍后重试");
                })
                .finally(() => {
                    isActionProcessing = false;
                    confirmButton.disabled = false;
                    confirmButton.textContent = "二次确认删除";
                });
            }
        </script>
	<script src="../static/js/admin-shell.js"></script>
</body>
</html>


