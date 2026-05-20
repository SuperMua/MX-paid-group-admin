<?php
require_once 'login_check.php';
require __DIR__ . '/../config/config.php';

function normalize_http_url($value, $forceTrailingSlash = false) {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    if (!preg_match('#^https?://#i', $value)) {
        $value = 'https://' . $value;
    }

    $parts = parse_url($value);
    if ($parts === false || empty($parts['host'])) {
        return false;
    }

    $scheme = strtolower($parts['scheme'] ?? 'https');
    if ($scheme !== 'http' && $scheme !== 'https') {
        return false;
    }

    $host = strtolower($parts['host']);
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    $path = isset($parts['path']) ? preg_replace('#/{2,}#', '/', $parts['path']) : '';

    if ($forceTrailingSlash) {
        $path = preg_replace('#/(submit|mapi|api)\.php$#i', '/', $path);
        $path = $path === '' ? '/' : rtrim($path, '/') . '/';
    } else {
        $path = rtrim($path, '/');
    }

    return $scheme . '://' . $host . $port . $path;
}

function normalize_callback_base($value) {
    $normalized = normalize_http_url($value, false);
    if ($normalized === false || $normalized === '') {
        return $normalized;
    }

    $normalized = preg_replace('#/pay/(notify_url|return_url)\.php$#i', '', $normalized);
    return rtrim($normalized, '/');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT api_url, merchant_id, secre_key, callback_url FROM payment LIMIT 1";
    $result = $conn->query($sql);

    header('Content-Type: application/json; charset=utf-8');
    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode(array('api_url' => '', 'merchant_id' => '', 'secre_key' => '', 'callback_url' => ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '不支持的请求方法';
    $conn->close();
    exit;
}

$apiUrlInput = (string)($_POST['api_url'] ?? '');
$merchantId = trim((string)($_POST['merchant_id'] ?? ''));
$secreKey = trim((string)($_POST['secre_key'] ?? ''));
$callbackInput = (string)($_POST['callback_url'] ?? '');

$apiUrl = normalize_http_url($apiUrlInput, true);
if ($apiUrlInput !== '' && $apiUrl === false) {
    http_response_code(422);
    echo '支付地址格式错误，请填写可访问的 HTTP/HTTPS 地址';
    $conn->close();
    exit;
}

$callbackUrl = normalize_callback_base($callbackInput);
if ($callbackInput !== '' && $callbackUrl === false) {
    http_response_code(422);
    echo '回调地址格式错误，请填写可访问的 HTTP/HTTPS 地址';
    $conn->close();
    exit;
}

$stmt = $conn->prepare("UPDATE payment SET api_url = ?, merchant_id = ?, secre_key = ?, callback_url = ? WHERE id = 1");
if (!$stmt) {
    http_response_code(500);
    echo '更新失败：SQL 准备错误';
    $conn->close();
    exit;
}

$stmt->bind_param('ssss', $apiUrl, $merchantId, $secreKey, $callbackUrl);
if ($stmt->execute()) {
    echo '更新成功';
} else {
    http_response_code(500);
    echo '更新失败：' . $stmt->error;
}

$stmt->close();
$conn->close();
?>
