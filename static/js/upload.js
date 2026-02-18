async function initImageUploader() {
    try {
        // 修改请求的 URL 为 settings_prompt.php
        const response = await fetch('settings_prompt.php');
        if (!response.ok) {
            throw new Error(`网络响应不正常: ${response.status} ${response.statusText}`);
        }
        const data = await response.json();

        const imageUploader = {
            // 允许的最大文件数量
            MAX_FILES: data.prompt,
            // 允许的最大文件大小，这里是 5MB
            MAX_FILES_SIZE: 5 * 1024 * 1024,
            // 存储选中的图片
            selectedImages: [],

            selectImage: function () {
                const input = document.createElement('input');
                input.type = 'file';
                input.multiple = true;
                input.accept = 'image/*';
                input.onchange = (event) => {
                    const files = event.target.files;
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        if (this.selectedImages.length >= this.MAX_FILES) {
                            alert(`最多只能选择 ${this.MAX_FILES} 张图片。`);
                            return;
                        }
                        if (file.size > this.MAX_FILES_SIZE) {
                            alert('文件大小超过限制，请选择小于 5MB 的图片。');
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = document.createElement('div');
                            img.className = 'image-item';
                            img.innerHTML = `<img src="${e.target.result}" alt=""><span class="close-button" onclick="imageUploader.removeImage(this)">x</span>`;
                            document.querySelector('.image-container').insertBefore(img, document.querySelector('.select-button'));
                            this.selectedImages.push(file);
                            this.updateSelectedCount();
                        };
                        reader.readAsDataURL(file);
                    }
                };
                input.click();
            },

            removeImage: function (button) {
                const imgItem = button.parentNode;
                const index = Array.from(imgItem.parentNode.children).indexOf(imgItem);
                this.selectedImages.splice(index - 1, 1);
                imgItem.remove();
                this.updateSelectedCount();
            },

            updateSelectedCount: function () {
                document.getElementById('selected-count').innerText = `已选择 ${this.selectedImages.length} 张，共${this.MAX_FILES}张图片`;
            },

            submitImages: function () {
                // 数量验证逻辑
                if (this.selectedImages.length < this.MAX_FILES) {
                    const countElement = document.getElementById('selected-count1');
                    countElement.innerText = `请按要求上传图片`;
                    return;
                }

                // 显示上传中提示信息
                let statusDiv = document.getElementById('upload-status');
                statusDiv.style.display = 'block';
                statusDiv.innerHTML = '上传中，请稍后...';

                const formData = new FormData();
                for (let i = 0; i < this.selectedImages.length; i++) {
                    formData.append('images[]', this.selectedImages[i]);
                }
                fetch('upload_handler.php', {
                    method: 'POST',
                    body: formData
                })
                   .then(response => response.json())
                   .then(data => {
                        // 处理上传结果
                        let statusDiv = document.getElementById('upload-status');
                        if (data.success) {
                            statusDiv.innerHTML = '上传成功！';
                            // 上传成功后跳转
                            setTimeout(() => {
                                window.location.href ='success_page.php'; // 上传成功后跳转
                            }, 1000); // 延迟 1 秒跳转
                        } else {
                            statusDiv.innerHTML = '上传失败：' + data.error;
                            // 上传失败后，过一段时间隐藏提示框
                            setTimeout(() => {
                                statusDiv.style.display = 'none';
                            }, 3000);
                        }
                    })
                   .catch(error => {
                        console.error('Error:', error);
                        let statusDiv = document.getElementById('upload-status');
                        statusDiv.innerHTML = '上传过程中发生错误：' + error;
                        // 发生错误后，过一段时间隐藏提示框
                        setTimeout(() => {
                            statusDiv.style.display = 'none';
                        }, 3000);
                    });
            }
        };

        return imageUploader;
    } catch (error) {
        console.error('请求出错:', error);
        // 返回默认的 imageUploader 对象
        return {
            MAX_FILES: 1,
            MAX_FILES_SIZE: 5 * 1024 * 1024,
            selectedImages: [],
            selectImage: function () {},
            removeImage: function () {},
            updateSelectedCount: function () {},
            submitImages: function () {}
        };
    }
}

var tanChuang = {
    openPopup1: function () {
        document.getElementById('mask1').style.display = 'flex';
        document.getElementById('popup1').style.display = 'block';
    },

    closePopup1: function () {
        document.getElementById('mask1').style.display = 'none';
        document.getElementById('popup1').style.display = 'none';
    },

    openPopup2: function () {
        document.getElementById('mask2').style.display = 'flex';
        document.getElementById('popup2').style.display = 'block';
    },

    closePopup2: function () {
        document.getElementById('mask2').style.display = 'none';
        document.getElementById('popup2').style.display = 'none';
    },
};

var tuPian = {
    downloadImage: function () {
        var imgSrc = document.getElementById('imgSrc').src;
        var link = document.createElement('a');
        link.href = imgSrc;
        link.download = '评论截图.png'; // 可以自定义下载的文件名
        link.click();
    }
};

var yanZheng = {
    yztp: function () {
        var modal = document.getElementById("myModal");
        var span = document.getElementsByClassName("close")[0];
        if (modal && span) {
            modal.style.display = "flex";
            span.onclick = function () {
                modal.style.display = "none";
            };
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            };
        } else {
            console.error("未找到 myModal 元素或 close 元素");
        }
    }
};

// 调用 yztp 函数的函数，确保页面元素已经加载
function callYztp() {
    var modal = document.getElementById("myModal");
    if (modal) {
        yanZheng.yztp();
    } else {
        // 等待一段时间后再尝试调用
        setTimeout(callYztp, 100);
    }
}

callYztp();
window.yanZheng = yanZheng;

// 初始化 imageUploader 对象
initImageUploader().then((imageUploader) => {
    window.imageUploader = imageUploader;
    window.mokuai = {
        tanChuang: tanChuang,
        tuPian: tuPian,
    };
});