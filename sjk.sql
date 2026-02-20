/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.2.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: qun555
-- ------------------------------------------------------
-- Server version	12.2.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Current Database: `qun555`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `qun555` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `qun555`;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES
(1,'admin','e10adc3949ba59abbe56e057f20f883e','2025-01-08','../static/images/17126036.jpg');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `auto_settings`
--

DROP TABLE IF EXISTS `auto_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `auto_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_auto_audit_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '自动审核开关状态，1: 开启，0: 关闭',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '状态更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `auto_settings` WRITE;
/*!40000 ALTER TABLE `auto_settings` DISABLE KEYS */;
INSERT INTO `auto_settings` VALUES
(1,1,'2026-02-20 17:17:57');
/*!40000 ALTER TABLE `auto_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `brand_settings`
--

DROP TABLE IF EXISTS `brand_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `brand_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(120) NOT NULL DEFAULT '付费进群系统',
  `logo_path` varchar(255) NOT NULL DEFAULT '',
  `favicon_path` varchar(255) NOT NULL DEFAULT '',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `brand_settings` WRITE;
/*!40000 ALTER TABLE `brand_settings` DISABLE KEYS */;
INSERT INTO `brand_settings` VALUES
(1,'MX付费进群系统','','','2026-02-20 09:02:13');
/*!40000 ALTER TABLE `brand_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `group_images`
--

DROP TABLE IF EXISTS `group_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_images`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `group_images` WRITE;
/*!40000 ALTER TABLE `group_images` DISABLE KEYS */;
INSERT INTO `group_images` VALUES
(57,1,'../static/images/17045867.png'),
(58,1,'../static/images/17011360.jpg');
/*!40000 ALTER TABLE `group_images` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `upload_time` datetime NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `reviewer` varchar(100) NOT NULL DEFAULT '管理员',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `ip_location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `images`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `images` WRITE;
/*!40000 ALTER TABLE `images` DISABLE KEYS */;
/*!40000 ALTER TABLE `images` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `order_number` char(20) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `ip_location` varchar(255) NOT NULL,
  `money` decimal(10,2) NOT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `payment_time` datetime NOT NULL,
  `payment_status` enum('已支付','未支付') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(2,'付费订单','20250527214107167','222.214.242.55','四川省 遂宁市',9.90,'wxpay','2025-05-27 21:41:07','已支付');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_url` varchar(255) NOT NULL,
  `merchant_id` varchar(50) NOT NULL,
  `secre_key` varchar(255) NOT NULL,
  `callback_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
INSERT INTO `payment` VALUES
(1,'','','','');
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_title` varchar(255) DEFAULT NULL,
  `sub_title` varchar(255) DEFAULT NULL,
  `entry_price` decimal(10,1) DEFAULT NULL,
  `group_description` text DEFAULT NULL,
  `question` text DEFAULT NULL,
  `group_avatar` varchar(255) DEFAULT NULL,
  `customer_service_image` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `warm_tip` text DEFAULT NULL,
  `ordering` varchar(20) DEFAULT NULL,
  `original_price` decimal(10,1) DEFAULT NULL,
  `reviews1` varchar(40) DEFAULT NULL,
  `reviews2` varchar(40) DEFAULT NULL,
  `reviews3` varchar(40) DEFAULT NULL,
  `reviews4` varchar(40) DEFAULT NULL,
  `reviews5` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
(1,'高质量搭子群','单身美女帅哥请进',9.9,'高质量交友搭子群，脱单群，长期更新！欢迎大家的加入！！！','1、打赏后怎么进群？</br></br>\r\n打赏后返回此页面，会自动显示群码，扫码进群即可。</br></br>\r\n2、打赏后能退歀吗？</br></br>\r\n自愿打赏，无论任何原因，打赏后概不退歀，介意勿扰。</br></br>\r\n3、群封了怎么办？</br></br>\r\n如遇封群，联系客服进新群！','../static/images/6821b407469ce_1.png','../static/images/6998393e8c45b.jpg','../static/images/699839418aef4.jpg','<li>两种方式进群</li>\r\n<li>1. 完成一个小任务进vip群</li>\r\n<li>2. 直接打赏9.9元进vip群</li>','立即加入群聊',19.9,'生活圈子小，这个群可以认识很多新朋友，强烈推荐！','进这个群太值了，感谢群主！','太感谢群主了','太感谢群主了','太感谢群主了');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `task_set`
--

DROP TABLE IF EXISTS `task_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '任务标题',
  `intro` text DEFAULT NULL COMMENT '任务介绍',
  `requirement` text DEFAULT NULL COMMENT '任务要求',
  `review_time` varchar(80) DEFAULT NULL,
  `download_img` varchar(255) DEFAULT NULL COMMENT '下载图片（存储图片路径）',
  `example_img` varchar(255) DEFAULT NULL COMMENT '示例图（存储图片路径）',
  `prompt` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_set`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `task_set` WRITE;
/*!40000 ALTER TABLE `task_set` DISABLE KEYS */;
INSERT INTO `task_set` VALUES
(1,'完成下面任务，即可免费进群','<p>第一步：保存下面带公众号的图片;</p>\r\n<p>第二步：去抖音/快手/小红书/B站各大短视频平台,搜索：呱群,吃瓜,爆料等关键词;</p>\r\n<p>第三步：找到任意吃呱贴视频,在评论区评论带公众号的图片,然后点赞自己的评论,并截图保存;</p>\r\n<p>第四步：重复以上操作,完成评论4个不同的呱贴视频,共4张截图,最后在此页上传4张截图,通过审核后即可进VIP群免费吃呱,之后就不需要做任何任务了哦!</p>','评论区发布图片，并点赞。','<p>10分钟,若长时间未审核,请联系客服处理。</p>\r\n<p>通过审核,自动弹出进群二维码,请注意查看。</p>','../static/images/17207794.jpg','../static/images/17768983.png',4);
/*!40000 ALTER TABLE `task_set` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `temp`
--

DROP TABLE IF EXISTS `temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` decimal(10,2) NOT NULL,
  `text_field` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temp`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `temp` WRITE;
/*!40000 ALTER TABLE `temp` DISABLE KEYS */;
INSERT INTO `temp` VALUES
(1,9.90,'立即加入群聊');
/*!40000 ALTER TABLE `temp` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `template`
--

DROP TABLE IF EXISTS `template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `group_avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `template` WRITE;
/*!40000 ALTER TABLE `template` DISABLE KEYS */;
INSERT INTO `template` VALUES
(21,'老司机交友部落','【分享】今日吃瓜合集....','../static/images/681f87651894b_kefu.png'),
(22,'搭子交友群','太迷人了[坏笑]','../static/images/681f879abed90_qun.png'),
(23,'搭子交友群','这个腿比我命还长[坏笑][坏笑][坏笑]','../static/images/681f87b44519f_qun.png'),
(24,'搭子交友群','请谨慎观看[视频]','../static/images/681f87bdd6cc9_qun.png'),
(25,'搭子交友群','这是一个真实故事，大家…','../static/images/681f87c7e206e_qun.png'),
(26,'搭子交友群','爆照[色]','../static/images/681f87d25e177_qun.png'),
(27,'搭子交友群','个人资料：98年，女，现…','../static/images/681f87dbe4395_qun.png'),
(28,'搭子交友群','[图片][图片]','../static/images/681f87e60d74e_qun.png'),
(29,'搭子交友群','这…？[视频]','../static/images/681f88074ff92_kefu.png'),
(30,'同城脱单交友群','[链接]太牛了…','../static/images/681f881264c2c_qun.png'),
(31,'搭子交友群','大家好[表情]','../static/images/681f881fb8820_qun.png'),
(32,'搭子交友群','[图]姐妹们，这…','../static/images/681f88296c53e_qun.png'),
(33,'高质量搭子群','@所有人  各位美女帅哥们…','../static/images/6821bbe35cecd_qun.png'),
(34,'高质量搭子群','这个有点炸裂[惊讶]…','../static/images/6821bd5ce0dd2_qun.png'),
(35,'高质量搭子群','[图片]你们怎么看…','../static/images/6821bd6791c33_qun.png'),
(36,'高质量搭子群','【惊喜】大家要不要一起…','../static/images/6821bd724cd85_qun.png'),
(37,'同城高质量搭子群','宝子们，我刚加入这个…','../static/images/6821bd7e48f84_qun.png'),
(38,'高质量搭子群','[视频][视频][视频]','../static/images/6821bd875535f_qun.png'),
(39,'高质量搭子群','周末有没有一起…','../static/images/6821bd9115c88_qun.png'),
(40,'高质量单身交友群','恋爱么？我是一个比较…','../static/images/6821bd9a43875_qun.png'),
(41,'高质量搭子群','大家好，很高兴加入…','../static/images/6821becf95256_kefu.png'),
(42,'同城搭子交友3群','你们平时喜欢做什么…','../static/images/6821bee8ab13d_qun.png'),
(43,'高质量搭子交友群','大家对未来的另一半有…','../static/images/6821bef79c356_qun.png'),
(44,'高质量搭子交友5群','最近有没有什么好看的…','../static/images/6821bf04e7b58_qun.png');
/*!40000 ALTER TABLE `template` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `visitors`
--

DROP TABLE IF EXISTS `visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(100) NOT NULL,
  `visit_time` datetime NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `ip_location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitors`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `visitors` WRITE;
/*!40000 ALTER TABLE `visitors` DISABLE KEYS */;
INSERT INTO `visitors` VALUES
(1,'127.0.0.1','电脑端','2026-02-18 17:59:19','/public/home_v1.php','未知'),
(2,'127.0.0.1','电脑端','2026-02-18 21:42:01','/public/home_v1.php','未知'),
(3,'127.0.0.1','电脑端','2026-02-18 21:42:44','/public/home_v1.php','未知'),
(4,'127.0.0.1','电脑端','2026-02-18 22:32:55','/public/home_v1.php','未知'),
(5,'127.0.0.1','iPhone','2026-02-18 22:33:22','/public/home_v1.php','未知'),
(6,'127.0.0.1','iPhone','2026-02-18 22:33:33','/public/upload.php','未知'),
(7,'127.0.0.1','iPhone','2026-02-18 22:44:03','/public/upload.php','未知'),
(8,'127.0.0.1','电脑端','2026-02-18 22:56:24','/public/home_v1.php','未知'),
(9,'127.0.0.1','电脑端','2026-02-18 22:56:29','/public/home_v2.php','未知'),
(10,'127.0.0.1','电脑端','2026-02-19 00:59:44','/public/home_v1.php','未知'),
(11,'127.0.0.1','电脑端','2026-02-19 08:15:55','/public/home_v1.php','未知'),
(12,'127.0.0.1','电脑端','2026-02-19 08:15:56','/public/home_v1.php','未知'),
(13,'127.0.0.1','电脑端','2026-02-19 08:15:56','/public/home_v1.php','未知'),
(14,'127.0.0.1','电脑端','2026-02-19 08:15:57','/public/home_v1.php','未知'),
(15,'127.0.0.1','电脑端','2026-02-19 08:16:32','/public/home_v1.php','未知'),
(16,'127.0.0.1','电脑端','2026-02-19 08:16:33','/public/home_v1.php','未知'),
(17,'127.0.0.1','电脑端','2026-02-19 08:16:34','/public/home_v1.php','未知'),
(18,'127.0.0.1','iPhone','2026-02-19 08:37:34','/public/home_v1.php','未知'),
(19,'127.0.0.1','电脑端','2026-02-19 08:57:47','/public/home_v2.php','未知'),
(20,'127.0.0.1','电脑端','2026-02-19 08:57:55','/public/home_v1.php','未知'),
(21,'127.0.0.1','电脑端','2026-02-19 09:12:18','/public/home_v1.php','未知'),
(22,'127.0.0.1','电脑端','2026-02-19 09:20:11','/public/home_v1.php','未知'),
(23,'127.0.0.1','电脑端','2026-02-19 09:20:18','/public/home_v2.php','未知'),
(24,'127.0.0.1','iPhone','2026-02-19 09:25:30','/public/home_v1.php','未知'),
(25,'127.0.0.1','电脑端','2026-02-19 09:27:24','/public/home_v1.php','未知'),
(26,'127.0.0.1','电脑端','2026-02-19 09:44:30','/public/home_v1.php','未知'),
(27,'127.0.0.1','电脑端','2026-02-19 10:00:49','/public/home_v1.php','未知'),
(28,'127.0.0.1','电脑端','2026-02-19 10:00:50','/public/home_v1.php','未知'),
(29,'127.0.0.1','电脑端','2026-02-19 10:42:29','/public/home_v1.php','未知'),
(30,'127.0.0.1','电脑端','2026-02-19 10:42:38','/public/home_v1.php','未知'),
(31,'127.0.0.1','电脑端','2026-02-19 23:32:24','/public/home_v1.php','未知'),
(32,'127.0.0.1','电脑端','2026-02-20 00:29:06','/public/home_v1.php','未知'),
(33,'127.0.0.1','电脑端','2026-02-20 00:29:11','/public/home_v2.php','未知'),
(34,'127.0.0.1','电脑端','2026-02-20 00:29:39','/public/home_v1.php','未知'),
(35,'127.0.0.1','电脑端','2026-02-20 00:40:30','/public/home_v1.php','未知'),
(36,'127.0.0.1','电脑端','2026-02-20 00:42:19','/public/home_v1.php','未知'),
(37,'127.0.0.1','电脑端','2026-02-20 00:42:34','/public/home_v1.php','未知'),
(38,'127.0.0.1','电脑端','2026-02-20 00:43:50','/public/home_v1.php','未知'),
(39,'127.0.0.1','电脑端','2026-02-20 00:51:04','/public/home_v1.php','未知'),
(40,'127.0.0.1','电脑端','2026-02-20 00:51:08','/public/home_v1.php','未知'),
(41,'127.0.0.1','电脑端','2026-02-20 00:51:21','/public/upload.php','未知'),
(42,'127.0.0.1','电脑端','2026-02-20 01:04:17','/public/upload.php','未知'),
(43,'127.0.0.1','电脑端','2026-02-20 01:04:18','/public/home_v1.php','未知'),
(44,'127.0.0.1','电脑端','2026-02-20 01:23:11','/public/home_v1.php','未知'),
(45,'127.0.0.1','电脑端','2026-02-20 01:29:17','/public/home_v1.php','未知'),
(46,'127.0.0.1','电脑端','2026-02-20 01:53:04','/public/home_v1.php','未知'),
(47,'127.0.0.1','电脑端','2026-02-20 01:53:26','/public/home_v1.php','未知'),
(48,'127.0.0.1','电脑端','2026-02-20 01:54:14','/public/home_v2.php','未知'),
(49,'127.0.0.1','电脑端','2026-02-20 02:15:02','/public/home_v2.php','未知'),
(50,'127.0.0.1','电脑端','2026-02-20 03:00:00','/public/home_v1.php','未知'),
(51,'127.0.0.1','电脑端','2026-02-20 03:50:32','/public/home_v1.php','未知'),
(52,'127.0.0.1','电脑端','2026-02-20 03:50:37','/public/home_v1.php','未知'),
(53,'127.0.0.1','电脑端','2026-02-20 03:56:23','/public/home_v1.php','未知'),
(54,'127.0.0.1','iPhone','2026-02-20 17:17:40','/public/home_v1.php','未知'),
(55,'127.0.0.1','iPhone','2026-02-20 17:17:47','/public/home_v1.php','未知'),
(56,'127.0.0.1','电脑端','2026-02-20 18:46:39','/public/home_v1.php','未知'),
(57,'127.0.0.1','iPhone','2026-02-20 18:46:54','/public/home_v1.php','未知'),
(58,'127.0.0.1','电脑端','2026-02-20 18:48:10','/public/home_v1.php','未知'),
(59,'127.0.0.1','iPhone','2026-02-20 18:48:18','/public/home_v1.php','未知'),
(60,'127.0.0.1','iPhone','2026-02-20 18:48:48','/public/upload.php','未知'),
(61,'127.0.0.1','iPhone','2026-02-20 18:57:54','/public/home_v1.php','未知'),
(62,'127.0.0.1','iPhone','2026-02-20 18:58:22','/public/home_v1.php','未知');
/*!40000 ALTER TABLE `visitors` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping events for database 'qun555'
--

--
-- Dumping routines for database 'qun555'
--

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-02-20 19:00:39
