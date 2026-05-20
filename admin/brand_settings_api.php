<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

function ensureBrandSettingsTable($conn) {
    $createTableSql = "CREATE TABLE IF NOT EXISTS brand_settings (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        brand_name VARCHAR(120) NOT NULL DEFAULT '付费进群系统',
        logo_path VARCHAR(255) NOT NULL DEFAULT '',
        favicon_path VARCHAR(255) NOT NULL DEFAULT '',
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($createTableSql)) {
        return false;
    }

    $countResult = $conn->query("SELECT COUNT(*) AS total FROM brand_settings");
    if (!$countResult) {
        return false;
    }
    $countRow = $countResult->fetch_assoc();
    $count = (int)($countRow['total'] ?? 0);
    if ($count === 0) {
        $conn->query("INSERT INTO brand_settings (brand_name, logo_path, favicon_path) VALUES ('付费进群系统', '', '')");
    }
    return true;
}

function getBrandSettings($conn) {
    $fallback = array(
        'brand_name' => '付费进群系统',
        'logo_path' => '',
        'favicon_path' => ''
    );

    if (!ensureBrandSettingsTable($conn)) {
        return $fallback;
    }

    $result = $conn->query("SELECT brand_name, logo_path, favicon_path FROM brand_settings ORDER BY id ASC LIMIT 1");
    if (!$result || $result->num_rows === 0) {
        return $fallback;
    }
    $row = $result->fetch_assoc();
    return array(
        'brand_name' => trim((string)($row['brand_name'] ?? $fallback['brand_name'])),
        'logo_path' => trim((string)($row['logo_path'] ?? '')),
        'favicon_path' => trim((string)($row['favicon_path'] ?? ''))
    );
}

function saveUploadedBrandImage($fieldName, $prefix) {
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return array('error' => '上传失败，请重试');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return array('error' => '图片大小不能超过 2MB');
    }

    $allowedMime = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'image/svg+xml' => 'svg'
    );

    $mimeType = mime_content_type($file['tmp_name']);
    if (!isset($allowedMime[$mimeType])) {
        return array('error' => '仅支持 JPG/PNG/WEBP/ICO/SVG 格式');
    }

    $extension = $allowedMime[$mimeType];
    $uploadDir = realpath(__DIR__ . '/../static');
    if ($uploadDir === false) {
        return array('error' => '静态目录不存在');
    }
    $brandingDir = $uploadDir . DIRECTORY_SEPARATOR . 'branding';
    if (!is_dir($brandingDir) && !mkdir($brandingDir, 0755, true)) {
        return array('error' => '品牌资源目录创建失败');
    }

    $filename = $prefix . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $extension;
    $targetPath = $brandingDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return array('error' => '图片保存失败，请检查目录权限');
    }

    return '/static/branding/' . $filename;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    $data = getBrandSettings($conn);
    echo json_encode(array('success' => true, 'data' => $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require_once 'login_check.php';

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method Not Allowed'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!ensureBrandSettingsTable($conn)) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => '品牌配置初始化失败'), JSON_UNESCAPED_UNICODE);
    exit;
}

$current = getBrandSettings($conn);
$brandName = trim((string)($_POST['brand_name'] ?? ''));
if ($brandName === '') {
    $brandName = $current['brand_name'];
}

$logoPath = $current['logo_path'];
$faviconPath = $current['favicon_path'];

$uploadedLogo = saveUploadedBrandImage('logo', 'brand_logo');
if (is_array($uploadedLogo) && isset($uploadedLogo['error'])) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'message' => $uploadedLogo['error']), JSON_UNESCAPED_UNICODE);
    exit;
}
if (is_string($uploadedLogo)) {
    $logoPath = $uploadedLogo;
}

$uploadedFavicon = saveUploadedBrandImage('favicon', 'brand_favicon');
if (is_array($uploadedFavicon) && isset($uploadedFavicon['error'])) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'message' => $uploadedFavicon['error']), JSON_UNESCAPED_UNICODE);
    exit;
}
if (is_string($uploadedFavicon)) {
    $faviconPath = $uploadedFavicon;
}

$updateSql = "UPDATE brand_settings SET brand_name = ?, logo_path = ?, favicon_path = ? WHERE id = (SELECT id FROM (SELECT id FROM brand_settings ORDER BY id ASC LIMIT 1) t)";
$stmt = $conn->prepare($updateSql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => '保存失败：SQL 准备错误'), JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->bind_param('sss', $brandName, $logoPath, $faviconPath);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => '保存失败：执行错误'), JSON_UNESCAPED_UNICODE);
    exit;
}

$saved = getBrandSettings($conn);
echo json_encode(array('success' => true, 'message' => '品牌设置已保存', 'data' => $saved), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
?>
