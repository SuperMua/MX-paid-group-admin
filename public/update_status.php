<?php
require __DIR__ . '/../config/config.php';

// 更新所有待审核的图片状态为“通过审核”
$stmt = $conn->prepare("UPDATE images SET status = 'approved' WHERE status = 'pending'");
$result = $stmt->execute();

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>