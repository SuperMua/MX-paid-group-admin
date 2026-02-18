 document.addEventListener('DOMContentLoaded', function() {
          // 加载现有设置数据
          loadSettings();
  
          // 处理群头像文件选择
          document.getElementById('group_avatar').addEventListener('change', function() {
              if (this.files && this.files[0]) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                      document.getElementById('group_avatar_preview').src = e.target.result;
                  };
                  reader.readAsDataURL(this.files[0]);
              }
          });
  
          // 处理客服二维码文件选择
          document.getElementById('customer_service_image').addEventListener('change', function() {
              if (this.files && this.files[0]) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                      document.getElementById('customer_service_image_preview').src = e.target.result;
                  };
                  reader.readAsDataURL(this.files[0]);
              }
          });
  
          // 处理入群二维码文件选择
          document.getElementById('qr_code').addEventListener('change', function() {
              if (this.files && this.files[0]) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                      document.getElementById('qr_code_preview').src = e.target.result;
                  };
                  reader.readAsDataURL(this.files[0]);
              }
          });
          
		
		         // 处理群图介绍文件选择
        document.getElementById('group_images_input').addEventListener('change', function() {
            var files = this.files;
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var reader = new FileReader();
                reader.onload = function(e) {
                    var imageItem = document.createElement('div');
                    imageItem.className = 'image-item';
                    imageItem.innerHTML = `
                        <img src="${e.target.result}" alt="群图介绍">
                        <button type="button" class="remove-image">x</button>
                    `;
                    // 修改：将新图片添加到开头
                     var imageList = document.getElementById('group_images_list');
                                        imageList.appendChild(imageItem);
                };
                reader.readAsDataURL(file);
            }
        });
  
          // 处理群图介绍图片的删除
          document.getElementById('group_images_list').addEventListener('click', function(event) {
              if (event.target.classList.contains('remove-image')) {
                  var imageItem = event.target.parentElement;
                  var imagePath = imageItem.querySelector('img').src;
                  // 去除可能的 Data URL 前缀，只保留文件路径
                  var filePath = imagePath.replace(/^data:image\/[a-z]+;base64,/, "");
                  var hiddenInput = document.createElement('input');
                  hiddenInput.type = 'hidden';
                  hiddenInput.name ='remove_images[]';
                  hiddenInput.value = filePath;
                  document.getElementById('settingsForm').appendChild(hiddenInput);
                  imageItem.remove();
              }
          });
  
          // 处理表单提交
          document.getElementById('settingsForm').addEventListener('submit', function(event) {
              var groupImages = [];
              var imageItems = document.querySelectorAll('#group_images_list img');
              imageItems.forEach(function(item) {
                  var imagePath = item.src;
                  // 去除可能的 Data URL 前缀，只保留文件路径
                  var filePath = imagePath.replace(/^data:image\/[a-z]+;base64,/, "");
                  groupImages.push(filePath);
              });
              var hiddenInput = document.createElement('input');
              hiddenInput.type = 'hidden';
              hiddenInput.name = 'group_images[]';
              hiddenInput.value = JSON.stringify(groupImages);
              this.appendChild(hiddenInput);
          });
  
          // 显示保存成功消息
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.has('success')) {
              const successMessage = document.getElementById('success-message');
              successMessage.style.display = 'block';
              setTimeout(() => {
                  successMessage.style.display = 'none';
              }, 3000);
          }
      });
  
      function loadSettings() {
          // 这里需要使用 AJAX 从服务器获取现有的设置数据
          var xhr = new XMLHttpRequest();
          xhr.open('GET','settings_crud.php?action=getSettings&id=1', true); // 假设获取 id 为 1 的设置，可根据实际情况修改
          xhr.onload = function () {
              if (xhr.status === 200) {
                  var settings = JSON.parse(xhr.responseText);original_price
				  document.getElementById('reviews5').value = settings.reviews5;
				  document.getElementById('reviews4').value = settings.reviews4;
				  document.getElementById('reviews3').value = settings.reviews3;
				  document.getElementById('reviews2').value = settings.reviews2;
				  document.getElementById('reviews1').value = settings.reviews1;
				  document.getElementById('original_price').value = settings.original_price;
				  document.getElementById('ordering').value = settings.ordering;
                  document.getElementById('group_title').value = settings.group_title;
                  document.getElementById('sub_title').value = settings.sub_title;
                  document.getElementById('entry_price').value = settings.entry_price;
                  document.getElementById('question').value = settings.question;
                  document.getElementById('group_description').value = settings.group_description;
                  document.getElementById('group_avatar_preview').src = '../' + settings.group_avatar;
                  document.getElementById('customer_service_image_preview').src = '../' + settings.customer_service_image;
                  document.getElementById('qr_code_preview').src = '../' + settings.qr_code;
                  document.getElementById('warm_tip').value = settings.warm_tip;
                  document.getElementById('id').value = settings.id;
  
                  // 加载群图介绍图片
                  var groupImages = settings.group_images;
                  if (groupImages && groupImages.length > 0) {
                      groupImages.forEach(function(image) {
                          var imageItem = document.createElement('div');
                          imageItem.className = 'image-item';
                          imageItem.innerHTML = `
                              <img src="../${image}" alt="群图介绍">
                              <button type="button" class="remove-image">x</button>
                          `;
                          document.getElementById('group_images_list').appendChild(imageItem);
                      });
                  }
              } else {
                  console.error('加载设置数据失败');
              }
          };
          xhr.onerror = function () {
              console.error('网络错误');
          };
          xhr.send();
      };
	  

const container = document.querySelector('.select-buttons-container');
    container.addEventListener('click', function (event) {
        if (event.target.tagName === 'SPAN') {
            const input = event.target.parentNode.querySelector('input[type="file"]');
            input.click();
        }
    });

    const allInputs = document.querySelectorAll('.select-button input[type="file"]');
    allInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            if (this.files.length > 0) {
                const imageList = document.getElementById('group_images_list');
                for (let i = 0; i < this.files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const imageItem = document.createElement('div');
                        imageItem.classList.add('image-item');
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = this.files[i].name;
                        imageItem.appendChild(img);
                        imageList.appendChild(imageItem);
                    };
                    reader.readAsDataURL(this.files[i]);
                }
            }
        });
    });
	  
	  
	  
	  
	  
	  
	  