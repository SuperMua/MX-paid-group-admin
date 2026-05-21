# MX付费进群系统

PHP 付费进群管理系统，用户通过微信/支付宝扫码支付后自动或审核入群。包含前端支付页面、后台管理面板、支付网关对接、访客追踪等完整功能。

---

## 目录结构

```
├── index.php                  # 入口路由：按模板参数分发到前端页面
├── sjk.sql                    # 数据库初始化SQL（MariaDB完整导出）
├── favicon.ico                # 站点图标
├── .htaccess                  # Apache URL 重写规则
│
├── config/
│   ├── config.php             # 数据库连接配置
│   └── record-visitor.php     # 访客记录模块
│
├── public/                    # 前端用户页面
│   ├── home_v1.php            # 模板1 - 前端支付主页
│   ├── home_v2.php            # 模板2 - 前端支付主页
│   ├── payment.php            # 支付下单与回调处理
│   ├── check.php              # 系统状态检查
│   ├── db.php                 # 前端数据库操作
│   ├── success_page.php       # 支付成功页
│   ├── settings_data.php      # 前端配置数据
│   ├── settings_prompt.php    # 弹窗提示配置
│   ├── update_status.php      # 订单状态更新
│   ├── image_upload.php       # 截图上传处理
│   ├── upload.php             # 文件上传
│   └── upload_handler.php     # 上传处理
│
├── admin/                     # 后台管理面板
│   ├── index.php              # 仪表盘（主控台）
│   ├── login.php              # 登录页
│   ├── login_process.php      # 登录鉴权
│   ├── login_check.php        # 登录状态校验
│   ├── logout.php             # 退出登录
│   ├── groups.php             # 数据聚合（已废弃，重定向到index.php）
│   ├── dashboard_data.php     # 仪表盘数据计算
│   ├── dashboard_api.php      # 仪表盘API接口
│   ├── order.php              # 订单管理
│   ├── order-backend.php      # 订单后端逻辑
│   ├── order_export.php       # 订单导出（CSV）
│   ├── review_list.php        # 审核任务列表
│   ├── review_details.php     # 审核详情
│   ├── review_db.php          # 审核数据库操作
│   ├── review_export.php      # 审核数据导出
│   ├── visitor.php            # 访客记录
│   ├── visitor_export.php     # 访客数据导出
│   ├── query-visitors.php     # 访客查询
│   ├── settings.php           # 系统设置（导航页）
│   ├── settings_page.php      # 通用设置页容器
│   ├── settings_page2.php     # 通用设置页容器v2
│   ├── settings_frontend.php  # 前端配置数据
│   ├── settings_backend.php   # 设置后端处理
│   ├── settings_crud.php      # 设置CRUD操作
│   ├── admin_settings.php     # 账号管理
│   ├── moban.php              # 模板设置
│   ├── payment_settings.php   # 支付配置
│   ├── payment_get.php        # 支付信息获取
│   ├── audit_status.php       # 审核开关管理
│   ├── auto_status.php        # 自动审核状态
│   ├── brand_settings.php     # 品牌设置页面
│   ├── brand_settings_api.php # 品牌信息API
│   ├── upload_avatar.php      # 头像上传
│   ├── upload_cache.php       # 缓存清理
│   ├── virtual_data.php       # 虚拟数据管理
│   ├── virtual_data_helper.php# 虚拟数据引擎
│   ├── seed_mock_data.php     # 模拟数据生成
│   └── users.php              # 用户管理
│
├── pay/                       # 支付模块
│   ├── epayapi.php            # 易支付API入口
│   ├── notify_url.php         # 支付异步通知回调
│   ├── return_url.php         # 支付同步跳转回调
│   └── lib/
│       ├── EpayCore.class.php # 易支付核心SDK
│       ├── api.config.php     # API配置
│       ├── epay.config.php    # 易支付配置
│       └── pay.php            # 支付参数构建
│
├── static/                    # 静态资源
│   ├── css/
│   │   ├── admin.css          # 后台全局样式（现代化面板）
│   │   ├── style.css          # 前端模板样式
│   │   ├── style2.css         # 前端模板2样式
│   │   ├── settings.css       # 设置页样式
│   │   ├── shenhe.css         # 审核页样式
│   │   └── upload.css         # 上传组件样式
│   ├── js/
│   │   ├── admin-shell.js     # 后台SPA导航框架
│   │   ├── chart.umd.min.js   # Chart.js 图表库
│   │   ├── script.js          # 前端交互脚本
│   │   ├── settings.js        # 设置页脚本
│   │   ├── upload.js          # 上传组件脚本
│   │   └── visitor.js         # 访客追踪脚本
│   ├── images/                # 静态图片资源
│   └── branding/              # 品牌资源（Logo/Favicon）
│
├── result/                    # 结果展示资源
│   ├── result.php             # 支付结果展示
│   ├── yz.php                 # 验证逻辑
│   ├── error.html             # 错误页面
│   ├── images/                # 展示图片（图标、二维码、头像等）
│   └── video/                 # 教程/展示视频
│
├── issues/                    # 工单/申诉页面
│   ├── complaint.html         # 投诉页面
│   ├── examine.html~5.html    # 审核状态展示模板
│   ├── form.html              # 表单页面
│   ├── success.html           # 成功提示页
│   └── tip.html               # 提示页
│
└── upload/                    # 用户上传文件目录
```

---

## 功能特性

### 用户端（前端）
- **双模板切换** — 支持 v1/v2 两套前端展示模板，通过 `?template=v2` 切换
- **微信/支付宝支付** — 对接彩虹易支付（Rainbow ePay）网关
- **支付截图上传** — 用户上传支付凭证，进入审核流程
- **自动入群展示** — 审核通过后展示群二维码/群链接
- **访客追踪** — IP归属地、访问时间、设备信息记录

### 管理端（后台）
- **仪表盘** — 总收入、今日收入、今日订单、访客数、转化率、7日趋势图、24小时分布、地区排名TOP5、最近订单
- **订单管理** — 订单列表、搜索筛选、支付状态管理（已支付/未支付）、订单删除、CSV导出
- **任务审核** — 待审核截图列表、审核通过/驳回、审核详情查看、批量导出
- **访客记录** — 访客列表、搜索、IP归属地信息、导出
- **系统设置** — 系统参数、前端展示文案配置
- **模板设置** — 模板选择、群信息、展示图片配置
- **任务设置** — 群组管理、入群条件配置
- **账号设置** — 管理员密码修改、头像上传
- **支付设置** — 易支付网关参数配置（商户ID、密钥、回调地址）
- **审核设置** — 自动审核开关
- **品牌设置** — 系统名称、Logo、Favicon 自定义
- **虚拟数据** — 内置虚拟数据引擎，可脱机演示全部功能
- **缓存清理** — 一键清理上传缓存

### 技术亮点
- **SPA 导航** — 后台使用 `admin-shell.js` 实现无刷新页面切换，带过渡动画
- **毛玻璃 UI** — 现代化玻璃态材质设计，自定义细滚动条
- **Chart.js 图表** — 趋势折线图、时段柱状图实时渲染
- **虚拟数据引擎** — 无需真实订单即可预览完整仪表盘
- **响应式布局** — 适配桌面端（≥992px）多列网格布局

---

## 环境要求

| 依赖 | 版本 |
|------|------|
| PHP | ≥ 7.4 |
| MySQL/MariaDB | ≥ 10.6 |
| Web Server | Apache（推荐）/ Nginx / PHP Built-in |

PHP 扩展：`mysqli`, `curl`, `json`, `session`, `gd`（图片处理）

---

## 快速部署

### 1. 导入数据库

```bash
# 创建数据库并导入
mysql -u root -p < sjk.sql
```

默认数据库名：`qun555`，如需修改请同步更新 `config/config.php`。

### 2. 配置数据库连接

编辑 `config/config.php`：

```php
$host = '127.0.0.1';      // 数据库主机
$username = 'qun555';      // 数据库用户名
$password = '你的密码';     // 数据库密码
$dbname = 'qun555';        // 数据库名
```

### 3. 配置支付网关

登录后台 → **支付设置**，填入彩虹易支付参数：
- 支付网关地址
- 商户 ID
- 商户密钥

### 4. 部署文件

将项目目录部署到 Web 服务器根目录（Apache 已含 `.htaccess`），或使用 PHP 内置服务器快速启动：

```bash
php -S 127.0.0.1:8080
```

### 5. 访问系统

| 入口 | URL |
|------|-----|
| 前端支付页 | `http://127.0.0.1:8080/` |
| 后台登录 | `http://127.0.0.1:8080/admin/login.php` |

**默认管理员账号**：`admin` / `123456`

---

## 数据库表结构

| 表名 | 说明 |
|------|------|
| `admin` | 管理员账号信息 |
| `orders` | 支付订单记录 |
| `images` | 用户上传支付截图 |
| `visitors` | 访客访问记录 |
| `settings` | 系统全局配置 |
| `template` | 前端模板配置 |
| `task_set` | 群组任务配置 |
| `payment` | 支付网关参数 |
| `brand_settings` | 品牌名称/Logo/Favicon |
| `group_images` | 群展示图片 |
| `auto_settings` | 自动审核开关 |
| `temp` | 模板临时数据 |

---

## 常见问题

**Q: 前端页面打不开？**
确认 Web 服务器已正确配置 rewrite 或使用 PHP 内置服务器。

**Q: 支付后未自动审核？**
检查后台 → 审核设置 → 自动审核是否已开启。

**Q: 图表不显示？**
确保 `static/js/chart.umd.min.js` 完整存在（~200KB），浏览器控制台检查 JS 报错。

**Q: 如何使用虚拟数据预览？**
后台 → 虚拟数据 → 生成虚拟数据，仪表盘将展示模拟统计数据。

---

## 许可与免责

本软件仅供个人学习和研究使用。禁止用于非法用途。详见源码头部声明。

---

## 作者

**MX团队** · 联系方式：78824824（QV同号）
