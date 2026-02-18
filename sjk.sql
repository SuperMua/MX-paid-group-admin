-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2025-05-27 23:29:27
-- 服务器版本： 5.7.44-log
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qun_chuangyouzy`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`id`, `name`, `password`, `date`, `avatar`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '2025-01-08', '../static/images/17706115.png');

-- --------------------------------------------------------

--
-- 表的结构 `auto_settings`
--

CREATE TABLE IF NOT EXISTS `auto_settings` (
  `id` int(11) NOT NULL,
  `is_auto_audit_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动审核开关状态，1: 开启，0: 关闭',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '状态更新时间'
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `auto_settings`
--

INSERT INTO `auto_settings` (`id`, `is_auto_audit_enabled`, `updated_at`) VALUES
(1, 1, '2025-05-25 08:44:34');

-- --------------------------------------------------------

--
-- 表的结构 `group_images`
--

CREATE TABLE IF NOT EXISTS `group_images` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `group_images`
--

INSERT INTO `group_images` (`id`, `group_id`, `image_path`) VALUES
(57, 1, '../static/images/17045867.png'),
(58, 1, '../static/images/17011360.jpg');

-- --------------------------------------------------------

--
-- 表的结构 `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `upload_time` datetime NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `reviewer` varchar(100) NOT NULL DEFAULT '管理员',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `ip_location` varchar(255) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `order_number` char(20) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `ip_location` varchar(255) NOT NULL,
  `money` decimal(10,2) NOT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `payment_time` datetime NOT NULL,
  `payment_status` enum('已支付','未支付') NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `orders`
--

INSERT INTO `orders` (`id`, `name`, `order_number`, `ip_address`, `ip_location`, `money`, `payment_method`, `payment_time`, `payment_status`) VALUES
(2, '付费订单', '20250527214107167', '222.214.242.55', '四川省 遂宁市', '9.90', 'wxpay', '2025-05-27 21:41:07', '已支付');

-- --------------------------------------------------------

--
-- 表的结构 `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL,
  `api_url` varchar(255) NOT NULL,
  `merchant_id` varchar(50) NOT NULL,
  `secre_key` varchar(255) NOT NULL,
  `callback_url` varchar(255) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `payment`
--

INSERT INTO `payment` (`id`, `api_url`, `merchant_id`, `secre_key`, `callback_url`) VALUES
(1, '', '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL,
  `group_title` varchar(255) DEFAULT NULL,
  `sub_title` varchar(255) DEFAULT NULL,
  `entry_price` decimal(10,1) DEFAULT NULL,
  `group_description` text,
  `question` text,
  `group_avatar` varchar(255) DEFAULT NULL,
  `customer_service_image` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `warm_tip` text,
  `ordering` varchar(20) DEFAULT NULL,
  `original_price` decimal(10,1) DEFAULT NULL,
  `reviews1` varchar(40) DEFAULT NULL,
  `reviews2` varchar(40) DEFAULT NULL,
  `reviews3` varchar(40) DEFAULT NULL,
  `reviews4` varchar(40) DEFAULT NULL,
  `reviews5` varchar(40) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `settings`
--

INSERT INTO `settings` (`id`, `group_title`, `sub_title`, `entry_price`, `group_description`, `question`, `group_avatar`, `customer_service_image`, `qr_code`, `warm_tip`, `ordering`, `original_price`, `reviews1`, `reviews2`, `reviews3`, `reviews4`, `reviews5`) VALUES
(1, '高质量搭子群', '单身美女帅哥请进', '9.9', '高质量交友搭子群，脱单群，长期更新！欢迎大家的加入！！！', '1、打赏后怎么进群？</br></br>\r\n打赏后返回此页面，会自动显示群码，扫码进群即可。</br></br>\r\n2、打赏后能退歀吗？</br></br>\r\n自愿打赏，无论任何原因，打赏后概不退歀，介意勿扰。</br></br>\r\n3、群封了怎么办？</br></br>\r\n如遇封群，联系客服进新群！', '../static/images/6821b407469ce_1.png', '../static/images/68261c1fc3199_kefu.png', '../static/images/68261c0ba6272_qun.png', '<li>两种方式进群</li>\r\n<li>1. 完成一个小任务进vip群</li>\r\n<li>2. 直接打赏9.9元进vip群</li>', '立即加入群聊', '19.9', '生活圈子小，这个群可以认识很多新朋友，强烈推荐！', '进这个群太值了，感谢群主！', '太感谢群主了', '太感谢群主了', '太感谢群主了');

-- --------------------------------------------------------

--
-- 表的结构 `task_set`
--

CREATE TABLE IF NOT EXISTS `task_set` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT '任务标题',
  `intro` text COMMENT '任务介绍',
  `requirement` text COMMENT '任务要求',
  `review_time` varchar(80) DEFAULT NULL,
  `download_img` varchar(255) DEFAULT NULL COMMENT '下载图片（存储图片路径）',
  `example_img` varchar(255) DEFAULT NULL COMMENT '示例图（存储图片路径）',
  `prompt` int(11) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `task_set`
--

INSERT INTO `task_set` (`id`, `title`, `intro`, `requirement`, `review_time`, `download_img`, `example_img`, `prompt`) VALUES
(1, '完成下面任务，即可免费进群', '<p>第一步：保存下面带公众号的图片;</p>\r\n<p>第二步：去抖音/快手/小红书/B站各大短视频平台,搜索：呱群,吃瓜,爆料等关键词;</p>\r\n<p>第三步：找到任意吃呱贴视频,在评论区评论带公众号的图片,然后点赞自己的评论,并截图保存;</p>\r\n<p>第四步：重复以上操作,完成评论4个不同的呱贴视频,共4张截图,最后在此页上传4张截图,通过审核后即可进VIP群免费吃呱,之后就不需要做任何任务了哦!</p>', '评论区发布图片，并点赞。', '<p>10分钟,若长时间未审核,请联系客服处理。</p>\r\n<p>通过审核,自动弹出进群二维码,请注意查看。</p>', '../static/images/17207794.jpg', '../static/images/17768983.png', 4);

-- --------------------------------------------------------

--
-- 表的结构 `temp`
--

CREATE TABLE IF NOT EXISTS `temp` (
  `id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `text_field` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `temp`
--

INSERT INTO `temp` (`id`, `amount`, `text_field`) VALUES
(1, '9.90', '立即加入群聊');

-- --------------------------------------------------------

--
-- 表的结构 `template`
--

CREATE TABLE IF NOT EXISTS `template` (
  `id` int(11) NOT NULL,
  `nickname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `group_avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `template`
--

INSERT INTO `template` (`id`, `nickname`, `content`, `group_avatar`) VALUES
(21, '老司机交友部落', '【分享】今日吃瓜合集....', '../static/images/681f87651894b_kefu.png'),
(22, '搭子交友群', '太迷人了[坏笑]', '../static/images/681f879abed90_qun.png'),
(23, '搭子交友群', '这个腿比我命还长[坏笑][坏笑][坏笑]', '../static/images/681f87b44519f_qun.png'),
(24, '搭子交友群', '请谨慎观看[视频]', '../static/images/681f87bdd6cc9_qun.png'),
(25, '搭子交友群', '这是一个真实故事，大家…', '../static/images/681f87c7e206e_qun.png'),
(26, '搭子交友群', '爆照[色]', '../static/images/681f87d25e177_qun.png'),
(27, '搭子交友群', '个人资料：98年，女，现…', '../static/images/681f87dbe4395_qun.png'),
(28, '搭子交友群', '[图片][图片]', '../static/images/681f87e60d74e_qun.png'),
(29, '搭子交友群', '这…？[视频]', '../static/images/681f88074ff92_kefu.png'),
(30, '同城脱单交友群', '[链接]太牛了…', '../static/images/681f881264c2c_qun.png'),
(31, '搭子交友群', '大家好[表情]', '../static/images/681f881fb8820_qun.png'),
(32, '搭子交友群', '[图]姐妹们，这…', '../static/images/681f88296c53e_qun.png'),
(33, '高质量搭子群', '@所有人  各位美女帅哥们…', '../static/images/6821bbe35cecd_qun.png'),
(34, '高质量搭子群', '这个有点炸裂[惊讶]…', '../static/images/6821bd5ce0dd2_qun.png'),
(35, '高质量搭子群', '[图片]你们怎么看…', '../static/images/6821bd6791c33_qun.png'),
(36, '高质量搭子群', '【惊喜】大家要不要一起…', '../static/images/6821bd724cd85_qun.png'),
(37, '同城高质量搭子群', '宝子们，我刚加入这个…', '../static/images/6821bd7e48f84_qun.png'),
(38, '高质量搭子群', '[视频][视频][视频]', '../static/images/6821bd875535f_qun.png'),
(39, '高质量搭子群', '周末有没有一起…', '../static/images/6821bd9115c88_qun.png'),
(40, '高质量单身交友群', '恋爱么？我是一个比较…', '../static/images/6821bd9a43875_qun.png'),
(41, '高质量搭子群', '大家好，很高兴加入…', '../static/images/6821becf95256_kefu.png'),
(42, '同城搭子交友3群', '你们平时喜欢做什么…', '../static/images/6821bee8ab13d_qun.png'),
(43, '高质量搭子交友群', '大家对未来的另一半有…', '../static/images/6821bef79c356_qun.png'),
(44, '高质量搭子交友5群', '最近有没有什么好看的…', '../static/images/6821bf04e7b58_qun.png');

-- --------------------------------------------------------

--
-- 表的结构 `visitors`
--

CREATE TABLE IF NOT EXISTS `visitors` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(100) NOT NULL,
  `visit_time` datetime NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `ip_location` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auto_settings`
--
ALTER TABLE `auto_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_images`
--
ALTER TABLE `group_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_set`
--
ALTER TABLE `task_set`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp`
--
ALTER TABLE `temp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `template`
--
ALTER TABLE `template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `auto_settings`
--
ALTER TABLE `auto_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `group_images`
--
ALTER TABLE `group_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=59;
--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `task_set`
--
ALTER TABLE `task_set`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `temp`
--
ALTER TABLE `temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `template`
--
ALTER TABLE `template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
