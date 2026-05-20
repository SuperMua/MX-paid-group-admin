<?php 
require_once 'login_check.php'; 
require_once __DIR__ . '/../config/config.php'; 
require_once 'virtual_data_helper.php';

 // 变量初始化 
 $totalApproved = 0;
 $totalPending = 0;
 $totalRejected = 0;
 $invalidIp = false;
 $success = false;
 $error = false;
$ip_address = '';
$result = null;
$useVirtualData = vd_is_enabled();
try {
    
 
    // 判断当前页面 
    if ($useVirtualData && basename($_SERVER['PHP_SELF']) == 'review_details.php') {
        $ip_address = trim($_GET['ip'] ?? '');
        $success = isset($_GET['success']) && $_GET['success'] === '1';
        $error = isset($_GET['error']) && $_GET['error'] === '1';

        if (empty($ip_address) || !filter_var($ip_address, FILTER_VALIDATE_IP)) {
            $invalidIp = true;
        }

        if (!$invalidIp) {
            $action = $_GET['action'] ?? null;
            $image_id = isset($_GET['image_id']) ? intval($_GET['image_id']) : null;
            if (in_array($action, array('approve', 'reject'), true) && $image_id) {
                $status = $action === 'approve' ? 'approved' : 'rejected';
                $updated = vd_update_review_status($image_id, $ip_address, $status);
                header("Location: review_details.php?ip=" . urlencode($ip_address) . ($updated ? "&success=1" : "&error=1"));
                exit;
            }
            $result = new VirtualArrayResult(vd_get_review_details($ip_address));
        }
    } elseif ($useVirtualData) {
        $recordsPerPage = 12;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $mockPayload = vd_get_review_list_payload($page, $recordsPerPage);
        $totalRecords = $mockPayload['totalRecords'];
        $totalPages = $mockPayload['totalPages'];
        $result = new VirtualArrayResult($mockPayload['rows']);
        $totalApproved = $mockPayload['totalApproved'];
        $totalPending = $mockPayload['totalPending'];
        $totalRejected = $mockPayload['totalRejected'];
    } elseif (basename($_SERVER['PHP_SELF']) == 'review_details.php')  {
        // 处理详情页逻辑 
        $ip_address = trim($_GET['ip'] ?? '');
        $success = isset($_GET['success']) && $_GET['success'] === '1';
        $error = isset($_GET['error']) && $_GET['error'] === '1';

        if (empty($ip_address) || !filter_var($ip_address, FILTER_VALIDATE_IP)) {
            $invalidIp = true;
        }

        if (!$invalidIp) {
            $action = $_GET['action'] ?? null;
            $image_id = isset($_GET['image_id']) ? intval($_GET['image_id']) : null;

            if (in_array($action, array('approve', 'reject'), true) && $image_id) {
                $status = $action === 'approve' ? 'approved' : 'rejected';
                $sql = "UPDATE images SET status = ? WHERE id = ? AND ip_address = ?";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
    
                if (!$stmt->bind_param("sis", $status, $image_id, $ip_address)) {
                    throw new Exception("Binding parameters failed: " . $stmt->error);
                }
    
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                if ($stmt->affected_rows > 0) {
                    header("Location: review_details.php?ip="  . urlencode($ip_address) . "&success=1");
                } else {
                    header("Location: review_details.php?ip="  . urlencode($ip_address) . "&error=1");
                }
                exit;
            }
     
            $sql = "SELECT id, filename, upload_time, ip_address, file_path, reviewer, status, ip_location 
                    FROM images 
                    WHERE ip_address = ? 
                    ORDER BY upload_time DESC";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
     
            $stmt->bind_param("s", $ip_address);
            $stmt->execute();
            $result = $stmt->get_result();
        }
    } else {
        // 处理列表页逻辑 
        // 分页相关参数 
        $recordsPerPage = 12; // 每页显示的记录数（桌面端按 3 列布局可完整铺满） 
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // 当前页数，默认为第一页 
        $offset = ($page - 1) * $recordsPerPage; // 计算偏移量 
 
        // 查询总记录数 
        $sqlTotal = "SELECT COUNT(DISTINCT ip_address) AS total FROM images";
        $resultTotal = $conn->query($sqlTotal);
 
        if (!$resultTotal) {
            throw new Exception("Query failed: " . $conn->error);
        }
 
        $totalRecords = $resultTotal->fetch_assoc()['total'] ?? 0;
 
        // 计算总页数 
        $totalPages = ceil($totalRecords / $recordsPerPage);
 
        // 带分页的 SQL 查询 
        $sql = "SELECT 
                ip_address, 
                MAX(ip_location) AS ip_location, 
                COUNT(*) AS image_count, 
                MAX(upload_time) AS latest_upload_time, 
                GROUP_CONCAT(status ORDER BY upload_time DESC) AS statuses 
                FROM images 
                GROUP BY ip_address 
                ORDER BY latest_upload_time DESC 
                LIMIT ?, ?";
                
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
 
        $stmt->bind_param("ii", $offset, $recordsPerPage);
        $stmt->execute();
        $result = $stmt->get_result();
    }
 
    if (!$useVirtualData) {
        // 新增：统计已审核和未审核的数量 
        $sqlStatusCounts = "SELECT 
                            (SELECT COUNT(DISTINCT ip_address) FROM images WHERE status = 'approved') AS approved_count,
                            (SELECT COUNT(DISTINCT ip_address) FROM images WHERE status = 'rejected') AS rejected_count,
                            (SELECT COUNT(DISTINCT ip_address) FROM images WHERE status = 'pending') AS pending_count 
                            FROM images";
        
        $statusStmt = $conn->prepare($sqlStatusCounts);
        
        if (!$statusStmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    
        $statusStmt->execute();
        $statusResult = $statusStmt->get_result();
        
        if ($statusRow = $statusResult->fetch_assoc()) {
            $totalApproved = $statusRow['approved_count'];
            $totalPending = $statusRow['pending_count'];
            $totalRejected = $statusRow['rejected_count'];
        }
    }
 
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
 
// 定义状态翻译函数 
function translate_status($status) {
    $status_map = array(
        'pending' => '待审核',
        'approved' => '已通过',
        'rejected' => '不通过'
    );
 
    return isset($status_map[$status]) ? $status_map[$status] : '未知状态';
}
 
// 分页相关变量（仅在列表页有效）
if (!isset($totalPages)) {
    $totalPages = 1;
}
if (!isset($page)) {
    $page = 1;
}
 
 
// 检查是否接收到清空请求
if (isset($_POST['clear_all'])) {
    if ($useVirtualData) {
        vd_clear_reviews();
        echo "操作成功";
    } else {
        // 执行清空表的SQL语句
        $sql = "TRUNCATE TABLE images"; // 或使用 DELETE FROM images
        if ($conn->query($sql) === TRUE) {
            echo "操作成功";
        } else {
            echo "操作失败: " . $conn->error;
        }
    }
    exit;
}
 
// 删除单个记录逻辑 
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['action'], $_POST['id']) &&
    $_POST['action'] === 'delete_single' &&
    !empty($_POST['id'])
) {
    $orderId = intval($_POST['id']);
    if ($useVirtualData) {
        vd_delete_review_by_id($orderId);
        echo "操作成功";
    } else {
        $sql = "DELETE FROM images WHERE id = ?";
        
        // 检查预处理语句是否创建成功 
        $stmt = null;
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $orderId);
            if ($stmt->execute()) {
                echo "操作成功";
            } else {
                error_log("删除失败: " . $stmt->error);
                echo "操作失败";
            }
            // 关闭预处理语句
            $stmt->close();
        } else {
            error_log("预处理失败: " . $conn->error);
            echo "操作失败";
        }
    }
    exit;
}

// 关闭数据库连接 
$conn->close();
?>
