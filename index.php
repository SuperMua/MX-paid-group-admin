<?php
/*****************************************************************************************/
/*                                * 免责声明 *                                            */
/*                                                                                       */
/* 版权：MX团队                                                                           */
/* 1. 本软件由MX团队开发，仅供个人学习和研究使用。                                          */
/* 2. 禁止用于非法用途，否则一切法律后果由用户自行承担。                                     */
/* 3. 未经许可，禁止商业交易、倒卖、转载、分发、修改、反向工程或用于未经授权的用途。          */
/* 4. 若软件源码或内容侵犯您权益，请及时联系处理并删除。                                     */
/* 5. 本声明最终解释权归MX团队所有，使用即视为同意声明内容。                                 */
/* 联系方式：78824824 QV同号                                                               */
/******************************************************************************************/

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$normalizedPath = rtrim($requestPath, '/');

if ($normalizedPath === '/admin') {
    header('Location: /admin/index.php');
    exit;
}

if ($normalizedPath === '' || $normalizedPath === '/index.php') {
    $template = strtolower(trim((string)($_GET['template'] ?? $_GET['tpl'] ?? 'v1')));
    $isTemplateV2 = in_array($template, ['2', 'v2', 'home_v2'], true);
    $homePage = $isTemplateV2 ? '/public/home_v2.php' : '/public/home_v1.php';
    header('Location: ' . $homePage);
    exit;
}

header('Location: /result/error.html');
exit;
?>
