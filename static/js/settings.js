document.addEventListener("DOMContentLoaded", function () {
    loadSettings();

    bindImagePreview("group_avatar", "group_avatar_preview");
    bindImagePreview("customer_service_image", "customer_service_image_preview");
    bindImagePreview("qr_code", "qr_code_preview");

    var groupImagesInput = document.getElementById("group_images_input");
    if (groupImagesInput) {
        groupImagesInput.addEventListener("change", function () {
            appendSelectedImages(this.files);
        });
    }

    var groupImagesList = document.getElementById("group_images_list");
    if (groupImagesList) {
        groupImagesList.addEventListener("click", function (event) {
            if (!event.target.classList.contains("remove-image")) {
                return;
            }

            var imageItem = event.target.closest(".image-item");
            if (!imageItem) {
                return;
            }

            var removeValue = imageItem.dataset.serverPath || "";
            if (!removeValue) {
                var image = imageItem.querySelector("img");
                removeValue = image ? image.src.replace(/^data:image\/[a-zA-Z0-9.+-]+;base64,/, "") : "";
            }

            if (removeValue) {
                var hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "remove_images[]";
                hiddenInput.value = removeValue;
                document.getElementById("settingsForm").appendChild(hiddenInput);
            }

            imageItem.remove();
        });
    }

    var settingsForm = document.getElementById("settingsForm");
    if (settingsForm) {
        settingsForm.addEventListener("submit", function () {
            var staleHiddenInputs = this.querySelectorAll("input[data-group-images-json='1']");
            staleHiddenInputs.forEach(function (input) {
                input.remove();
            });

            var groupImages = [];
            var imageItems = document.querySelectorAll("#group_images_list .image-item");
            imageItems.forEach(function (item) {
                if (item.dataset.serverPath) {
                    groupImages.push(item.dataset.serverPath);
                    return;
                }

                var image = item.querySelector("img");
                if (image && image.src) {
                    groupImages.push(image.src.replace(/^data:image\/[a-zA-Z0-9.+-]+;base64,/, ""));
                }
            });

            var hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.name = "group_images[]";
            hiddenInput.value = JSON.stringify(groupImages);
            hiddenInput.setAttribute("data-group-images-json", "1");
            this.appendChild(hiddenInput);
        });
    }

    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("success")) {
        var successMessage = document.getElementById("success-message");
        if (successMessage) {
            successMessage.style.display = "block";
            setTimeout(function () {
                successMessage.style.display = "none";
            }, 3000);
        }
    }

    var container = document.querySelector(".select-buttons-container");
    if (container) {
        container.addEventListener("click", function (event) {
            if (event.target.tagName !== "SPAN") {
                return;
            }

            var selectButton = event.target.closest(".select-button");
            if (!selectButton) {
                return;
            }

            var input = selectButton.querySelector("input[type='file']");
            if (input) {
                input.click();
            }
        });
    }
});

function bindImagePreview(inputId, previewId) {
    var input = document.getElementById(inputId);
    var preview = document.getElementById(previewId);
    if (!input || !preview) {
        return;
    }

    input.addEventListener("change", function () {
        if (!this.files || !this.files[0]) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });
}

function createImageItem(imageSrc, serverPath) {
    var imageItem = document.createElement("div");
    imageItem.className = "image-item";
    if (serverPath) {
        imageItem.dataset.serverPath = serverPath;
    }

    var img = document.createElement("img");
    img.src = imageSrc;
    img.alt = "群图介绍";
    imageItem.appendChild(img);

    var removeButton = document.createElement("button");
    removeButton.type = "button";
    removeButton.className = "remove-image";
    removeButton.textContent = "x";
    imageItem.appendChild(removeButton);

    return imageItem;
}

function appendSelectedImages(files) {
    if (!files || files.length === 0) {
        return;
    }

    var imageList = document.getElementById("group_images_list");
    if (!imageList) {
        return;
    }

    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var reader = new FileReader();
        reader.onload = function (e) {
            imageList.appendChild(createImageItem(e.target.result, ""));
        };
        reader.readAsDataURL(file);
    }
}

function loadSettings() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "settings_crud.php?action=getSettings&id=1", true);
    xhr.onload = function () {
        if (xhr.status !== 200) {
            console.error("加载设置数据失败");
            return;
        }

        var settings;
        try {
            settings = JSON.parse(xhr.responseText);
        } catch (error) {
            console.error("解析设置数据失败", error);
            return;
        }

        if (!settings || typeof settings !== "object") {
            return;
        }

        setInputValue("reviews5", settings.reviews5);
        setInputValue("reviews4", settings.reviews4);
        setInputValue("reviews3", settings.reviews3);
        setInputValue("reviews2", settings.reviews2);
        setInputValue("reviews1", settings.reviews1);
        setInputValue("original_price", settings.original_price);
        setInputValue("ordering", settings.ordering);
        setInputValue("group_title", settings.group_title);
        setInputValue("sub_title", settings.sub_title);
        setInputValue("entry_price", settings.entry_price);
        setInputValue("question", settings.question);
        setInputValue("group_description", settings.group_description);
        setInputValue("warm_tip", settings.warm_tip);
        setInputValue("id", settings.id);

        setImageSrc("group_avatar_preview", settings.group_avatar ? "../" + settings.group_avatar : "");
        setImageSrc(
            "customer_service_image_preview",
            settings.customer_service_image ? "../" + settings.customer_service_image : ""
        );
        setImageSrc("qr_code_preview", settings.qr_code ? "../" + settings.qr_code : "");

        var groupImages = [];
        if (Array.isArray(settings.group_images)) {
            groupImages = settings.group_images;
        }

        var groupImagesList = document.getElementById("group_images_list");
        if (groupImagesList) {
            groupImagesList.innerHTML = "";
            groupImages.forEach(function (imagePath) {
                if (!imagePath) {
                    return;
                }
                groupImagesList.appendChild(createImageItem("../" + imagePath, imagePath));
            });
        }
    };

    xhr.onerror = function () {
        console.error("网络错误");
    };
    xhr.send();
}

function setInputValue(id, value) {
    var input = document.getElementById(id);
    if (input) {
        input.value = value || "";
    }
}

function setImageSrc(id, src) {
    var image = document.getElementById(id);
    if (image && src) {
        image.src = src;
    }
}
