<?php
// 引入数据库配置文件
require_once __DIR__ . '/../config/config.php';
require_once 'login_check.php';  
require_once 'virtual_data_helper.php';

// 定义支付方式的映射数组
$paymentMethodMap = [
    'wxpay' => '微信',
    'alipay' => '支付宝'
];

// 每页显示的订单数量（桌面端按 3 列布局可完整铺满）
$perPage = 12;
// 获取当前页码，默认为第一页
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
// 计算偏移量
$offset = ($page - 1) * $perPage;

if (vd_is_enabled()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'delete_all') {
                vd_clear_orders();
                echo "操作成功";
            } elseif ($_POST['action'] === 'delete_single' && !empty($_POST['order_id'])) {
                vd_delete_order_by_id((int) $_POST['order_id']);
                echo "操作成功";
            } else {
                echo "操作失败";
            }
        }
        exit;
    }

    $mockPayload = vd_get_orders_page($page, $perPage);
    $orders = $mockPayload['orders'];
    $countRow = $mockPayload['countRow'];
    $conn->close();
    return;
}

// 查询订单列表，包括支付状态
$ordersSql = "SELECT * FROM orders ORDER BY payment_time DESC LIMIT?,?";
$ordersStmt = $conn->prepare($ordersSql);
$ordersStmt->bind_param("ii", $offset, $perPage);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();
$orders = $ordersResult->fetch_all(MYSQLI_ASSOC);

// 计算总订单量、已支付订单量和未支付订单量
$countSql = "SELECT 
                COUNT(*) AS total_orders,
                COUNT(CASE WHEN payment_status = '已支付' THEN 1 END) AS paid_orders,
                COUNT(CASE WHEN payment_status = '未支付' THEN 1 END) AS unpaid_orders
            FROM orders";
$countStmt = $conn->prepare($countSql);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();

//删除记录
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete_all') {
            $sql = "TRUNCATE TABLE orders"; // 或使用 DELETE FROM orders
            if ($conn->query($sql) === TRUE) {
                echo "操作成功";
            } else {
                echo "删除失败: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'delete_single' && !empty($_POST['order_id'])) {
            $orderId = intval($_POST['order_id']);
            $sql = "DELETE FROM orders WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $orderId);
            if ($stmt->execute()) {
                echo "操作成功";
            } else {
                echo "删除失败: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// 关闭数据库连接
$conn->close();
?>
