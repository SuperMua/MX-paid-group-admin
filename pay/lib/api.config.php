<?php
 // 引入数据库配置文件
  require_once __DIR__ . '/../../config/config.php';
  
  // 创建数据库连接
  $conn = new mysqli($host, $username, $password, $dbname);
  // 检查连接是否成功
  if ($conn->connect_error) {
      die("连接失败: ". $conn->connect_error);
  }
  
  // 查询 payment 表中的所有数据，取第一条记录
  $sql = "SELECT * FROM payment LIMIT 1";
  $result = $conn->query($sql);
  
  // 检查查询是否失败
  if ($result === false) {
      echo "查询失败: ". $conn->error;
  } else {
      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $baseUrl = $row['api_url']; 
          $merchantId = $row['merchant_id'];
          $secreKey = $row['secre_key'];
          $callbackUrl = $row['callback_url'];
      } else {
          echo "无数据";
      }
  }
  
  // 关闭数据库连接
  $conn->close();
 
 ?>