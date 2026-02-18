 function showVideo(item){
            // 隐藏所有视频和播放按钮
            var videoItems = document.querySelectorAll('.video-item');
            videoItems.forEach(function(videoItem){
                var img = videoItem.querySelector('img');
                var video = videoItem.querySelector('.video-player');
                var playButton = videoItem.querySelector('.play-button');
                img.style.display = 'block';
                video.style.display = 'none';
                playButton.style.display = 'block';
                video.pause();
                video.currentTime = 0;
                videoItem.classList.remove('playing');
            });

            // 显示当前视频，隐藏播放按钮
            var img = item.querySelector('img');
            var video = item.querySelector('.video-player');
            var playButton = item.querySelector('.play-button');
            img.style.display = 'none';
            video.style.display = 'block';
            playButton.style.display = 'none';
            video.play();
            item.classList.add('playing');

            
            //点击视频弹窗
                setTimeout(function(){
                    video.pause();
                    document.getElementById('popupsp').style.display = 'block';
                    document.getElementById('overlaysp').style.display = 'block';
                }, 10000);
            }
            //关闭弹窗
            function closePopupsp(){
                document.getElementById('popupsp').style.display = 'none';
                document.getElementById('overlaysp').style.display = 'none';
            } 
           // 支付方式选择
           function toggleRadio(id) {
                  document.getElementById(id).checked = true;
            }  



 (function() {
         //支付弹窗
          function openPopup() {
              document.getElementById('popup').style.display = 'block';
              document.getElementById('overlay').style.display = 'block';
          }
          // 关闭弹窗
          function closePopup() {
              document.getElementById('popup').style.display = 'none';
              document.getElementById('overlay').style.display = 'none';
          }
          
         //客服弹窗
         function openModal() {
               document.getElementById('modal').style.display = 'block';
               document.getElementById('modal-overlay').style.display = 'block';
                 }
         // 关闭弹窗
         function closeModal() {
               document.getElementById('modal').style.display = 'none';
               document.getElementById('modal-overlay').style.display = 'none';
           }
		   // 将函数暴露到全局作用域
		      window.openPopup = openPopup;
		      window.closePopup = closePopup;
			  window.openModal = openModal;
			  window.closeModal = closeModal;
			  
        })();

(function() {
    let countdownTime = 28 * 52; // 30分钟倒计时，单位秒
    
          function updateCountdown() {
              const hours = Math.floor(countdownTime / 3600);
              const minutes = Math.floor((countdownTime % 3600) / 60);
              const seconds = countdownTime % 60;
    
              document.getElementById('countdownHours').textContent = hours.toString().padStart(2, '0');
              document.getElementById('countdownMinutes').textContent = minutes.toString().padStart(2, '0');
              document.getElementById('countdownSeconds').textContent = seconds.toString().padStart(2, '0');
    
              countdownTime--;
          }
    
          setInterval(updateCountdown, 1000);
})();


// 加载完成弹窗功能
 (function() {
    function showPopup() {
        document.querySelector('.masktx').style.display = 'block';
        document.querySelector('.popuptx').style.display = 'block';
    }

    function closePopup1() {
        document.querySelector('.masktx').style.display = 'none';
        document.querySelector('.popuptx').style.display = 'none';
    }

    window.showPopup = showPopup;
    window.closePopup1 = closePopup1;
})();

// 头部信息弹出功能
(function() {
    const infos = [
		"弘毅*** 刚刚支付了9.9元",
		"李涛*** 刚刚支付了9.9元",
		"豪哥*** 刚刚支付了9.9元",
		"海阔天空*** 刚刚支付了9.9元",
		"博文*** 刚刚支付了9.9元",
		"小蓝*** 刚刚支付了9.9元",
		"吴哥*** 刚刚支付了9.9元",
		"丰富*** 刚刚支付了9.9元",
		"格局*** 刚刚支付了9.9元",
		"天天歌*** 刚刚支付了9.9元",
		"云梦*** 刚刚支付了9.9元",
		"晴天*** 刚刚支付了9.9元",
		"黑羽*** 刚刚支付了9.9元",
		"宝宝*** 刚刚支付了9.9元"
		];
	
    const infoContainer = document.getElementById('infoContainer');
    let currentIndex = 0;

    function showInfo() {
        infoContainer.textContent = infos[currentIndex];
        infoContainer.style.display = 'block';
        infoContainer.style.right = '9%';
        infoContainer.style.opacity = 1;

        setTimeout(() => {
            infoContainer.style.opacity = 0;
            infoContainer.style.right = '-100%';
        }, 2000);

        setTimeout(() => {
            currentIndex = (currentIndex + 1) % infos.length;
            showInfo();
        }, 5000);
    }

    window.showInfo = showInfo;
})();

// 页面加载完成后自动弹出弹窗和信息提示
window.addEventListener('load', function() {
    setTimeout(showPopup, 2000); // 2秒后弹出弹窗
    setTimeout(showInfo, 3000); // 3秒后显示信息
});


		