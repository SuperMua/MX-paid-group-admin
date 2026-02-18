document.addEventListener('DOMContentLoaded', function() {
    // 获取当前时间
    const now = new Date();
    // 获取年、月、日、时、分、秒
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    // 转换为 YYYY-MM-DD HH:MM:SS 格式
    const visitTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

    // 收集访客信息
    const visitorData = {
        // 只保留设备类型
        userAgent: getDeviceType(), 
        visitTime: visitTime,
        pageUrl: window.location.pathname // 只获取路径部分
    };

    // 发送访客信息到服务器
    sendVisitorData(visitorData);
});

//获取设备系统
// 获取设备系统
function getDeviceType() {
    const userAgent = navigator.userAgent;
    if (/HarmonyOS/.test(userAgent)) {
        return '鸿蒙';
    }
    else if (/Android/.test(userAgent)) {
        return '安卓';
    } 
    else if (/iPhone/.test(userAgent)) {
        return 'iPhone';
    } else if (/iPad/.test(userAgent)) {
        return 'iPad';
    } else if (/iPod/.test(userAgent)) {
        return 'iPod';
    } 
    // 判断是否为电脑端系统
    else if (/Windows|Macintosh|Linux/.test(userAgent)) {
        return '电脑端';
    } 
    // 若都不匹配，返回未知设备
    else {
        return '未知设备';
    }
}

//获取设备型号
function getDeviceModel() {
    const userAgent = navigator.userAgent;

    if (/Android/.test(userAgent)) {
        const modelMatch = userAgent.match(/(OPPO|HUAWEI|Xiaomi|Samsung|LG|Sony|HTC|OnePlus|Nokia|Google|Motorola|ASUS|Realme|Vivo)/i);
        return modelMatch? modelMatch[0] : '未知型号';
    } else if (/iPhone/.test(userAgent)) {
        const modelMatch = userAgent.match(/iPhone\s(\d+)/);
        return modelMatch? `iPhone ${modelMatch[1]}` : '未知型号';
    } else {
        return '未知型号';
    }
}

function sendVisitorData(data) {
    fetch('../../config/record-visitor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
  .then(response => response.json())
  .then(data => {
        console.log('访客记录成功:', data);
    })
  .catch(error => {
        console.error('访客记录失败:', error);
    });
}