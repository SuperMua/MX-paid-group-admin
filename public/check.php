<?php
//验证是否已支付
// 引入数据库配置文件
require '../config/config.php';

// 创建数据库连接
$conn = new mysqli($host, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 获取当前IP地址
$userIP = $_SERVER['REMOTE_ADDR'];

// 查询对应IP地址的最新订单
$sql = "SELECT id, payment_status FROM orders WHERE ip_address = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("查询准备失败: " . $conn->error);
}

$stmt->bind_param("s", $userIP);
$stmt->execute();
$result = $stmt->get_result();

// 检查查询结果
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $paymentStatus = $row['payment_status'];
    $orderId = $row['id'];

    if ($paymentStatus == '已支付') {
        // 已支付，跳转到结果页面
        header("Location: ../result/result.php?status=success&order_id=" . $orderId);
        exit;
    } else {
        // 未支付，留在当前页面
        // 可以在这里添加一些提示信息，告知用户订单未支付
        echo "";
    }
} else {
    // 没有找到订单或查询失败，留在当前页面
    echo "";
}

// 关闭语句和数据库连接
$stmt->close();
$conn->close();


// 获取用户IP地址
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$userIP = getUserIP();

$ipLocation = '未知';
$data = ['groups' => []];
// 定义预设城市列表
$cities = ["北京", "上海", "广州", "深圳", "成都", "杭州", "南京", "武汉", "西安", "天津"];
// 初始化城市为随机预设城市（默认值）
$city = $cities[array_rand($cities)];

// 调用IP解析接口
$apiData = @file_get_contents("https://api.vore.top/api/IPdata?ip=" . urlencode($userIP));
if ($apiData) {
    $decoded = json_decode($apiData, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
    // 仅当接口返回有效城市且不是“基站”时，才覆盖默认值
    if (isset($data['adcode']['p']) &&!empty($data['adcode']['p']) && $data['adcode']['p']!== '基站') {
        $city = $data['adcode']['p'];
    }
}

// 获取当前时间
$currentTime = date('Y-m-d H:i:s'); 

$nicknames = [
      "踏月而来", "顾殇璃", "东西南北客", "那抹、憨笑", "斜月吟风",
      "毁忆", "甜蜜的年华", "夜琴灵", "夏日、樱花", "燕如兮",
      "我的温柔喂过狗", "愿你与菊花同在", "扯淡你的卖弄", "忽如远行客", "陋巷",
      "黑色的嫁衣", "江山未老", "小思绪", "浅笑倾城", "娇纵浪人",
      "花落人散去", "初欢", "梅幽香更远", "陷阱里的王子", "别在老娘面前拽",
      "对白栏", "天生、萌妹纸", "早知是梦", "冰雪玫瑰", "岁就超神ㄩ",
      "轻抚琴", "淺唱寂寞", "可爱多又多", "无人及你", "迟暮忘子矜",
      "钻石般闪耀女人", "请你给我来信", "我爱你与你无关", "白衣俊郎", "向谁诉说曾经",
      "柒屿", "暖瑾", "┏脑壳有包┓", "笑如烟霞",
	  "情绪饱满", "光洒雨衫", "悔恨当初", "临晚心", "一脸软萌融你心",
	  "木槿七七", "昨夜西风", "夏晕冬藏", "南峄", "祝白头",
	  "纪香", "大猪蹄子", "春风十里不如你", "冷曦ゝ", "海与迟落梦",
	  "月下饮茶", "心有所属", "零榆", "良人不知深情", "寄晴",
	  "帅***在此", "累却坚持i", "乖囧貓", "七夜", "梦若羽",
	  "star、枪神", "傲骨", "ヤ独霸怡荭院メ", "情话两行", "吟鸢",
	  "趁痛独饮醉", "樱花亦落", "狼族霸者", "蓝莓味少女", "那年盛夏っ",
	  "小酒窝的妩媚", "顾殇璃", "烈酒孤独", "踏月而来", "东西南北客"
    ];

    // 打乱数组顺序
    shuffle($nicknames);
    
    // 先校验 $data['groups'] 类型
     if (!is_array($data['groups'])) {
        $data['groups'] = []; // 非数组则赋空数组，避免 count 报错
     }
    // 确保网名数组的长度足够覆盖 $data['groups'] 的长度
    if (count($nicknames) < count($data['groups'])) {
        die("显示错误");
    }

// 提取前5个名字
$selectedNames = array_slice($nicknames, 0, 5);

// 将5个名字分别赋值给变量
$variable1 = $selectedNames[0];
$variable2 = $selectedNames[1];
$variable3 = $selectedNames[2];
$variable4 = $selectedNames[3];
$variable5 = $selectedNames[4];

// 生成头像路径数组
$avatarPool = [];
for ($i = 0; $i <= 20; $i++) {
    $avatarPool[] = "../result/images/tx{$i}.jpg";
}

// 确保头像数组中有足够的头像
if (count($avatarPool) < 20) {
    die("显示错误！");
}

// 打乱头像数组顺序
shuffle($avatarPool);

// 提取前5个头像路径
$selectedAvatars1 = array_slice($avatarPool, 0, 5);

// 提取7个头像路径，确保与第一组不重复
$selectedAvatars2 = array_slice($avatarPool, 5, 7);

// 将5个头像路径分别赋值给5个变量
$avatar1 = $selectedAvatars1[0];
$avatar2 = $selectedAvatars1[1];
$avatar3 = $selectedAvatars1[2];
$avatar4 = $selectedAvatars1[3];
$avatar5 = $selectedAvatars1[4];

// 将7个头像路径分别赋值给7个变量
$pavatar1 = $selectedAvatars2[0];
$pavatar2 = $selectedAvatars2[1];
$pavatar3 = $selectedAvatars2[2];
$pavatar4 = $selectedAvatars2[3];
$pavatar5 = $selectedAvatars2[4];
$pavatar6 = $selectedAvatars2[5];
$pavatar7 = $selectedAvatars2[6];

// 生成一个包含所有三位数的数组
$numberPool = range(100, 999);

// 打乱这个数组的顺序
shuffle($numberPool);

// 从打乱后的数组中提取前5个数
$selectedNumbers = array_slice($numberPool, 0, 5);

// 将这5个数分别赋值给5个变量
$number1 = $selectedNumbers[0];
$number2 = $selectedNumbers[1];
$number3 = $selectedNumbers[2];
$number4 = $selectedNumbers[3];
$number5 = $selectedNumbers[4];

?>
