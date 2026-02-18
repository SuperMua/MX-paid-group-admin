<?php
/*****************************************************************************************/
/*                                * 免责声明 *                                            */
/*                                                                                       */
/* 作者：胜天 *                                                                           */
/* 1. 本软件由作者独立开发，仅供个人学习和研究使用。                                        */
/* 2. 禁止用于非法用途，否则一切法律后果由用户自行承担，作者不负责。                         */
/* 3. 未经作者许可，禁止商业交易、倒卖、转载、分发、修改、反向工程或用于未经授权的用途。      */
/* 4. 若软件源码或内容侵犯您权益，请及时联系作者处理并删除。                                 */
/* 5. 本声明最终解释权归作者所有，使用即视为同意声明内容。                                   */
/* 联系方式邮箱：516615710@qq.com                                                          */
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
