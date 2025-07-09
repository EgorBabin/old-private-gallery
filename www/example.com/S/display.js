window.notificationTimer = null;

const lightbox = document.getElementById("lightbox");
const lightboxImg = lightbox.querySelector("img");

const notification = document.getElementById("notification");
const videoboxcontainer = document.querySelector(".videobox-container");
const videobox = document.getElementById("videobox");
const videoboxSource = videobox.querySelector("source");

// Функция для получения параметров из URL
function getUrlParam(name) {
    const params = new URLSearchParams(window.location.search);
    return params.get(name) || "";
}

// Получаем путь URL
const path = window.location.pathname;
// Разделяем путь на части и убираем пустые строки
const parts = path.split('/').filter(part => part !== '');
// Извлекаем year и category
const year = parts[0];
const category = parts[1];

document.querySelectorAll(".photo").forEach(photo => {
    photo.addEventListener("click", () => {
        if (photo.alt === "image") {
            const imageAlt = encodeURIComponent(photo.id); // Кодируем alt для URL
            const imageUrl = `https://example.com/image.php?path=p/${year}/${category}/org/${imageAlt}.jpg`;
            lightboxImg.src = imageUrl;
            lightbox.classList.add("show");
            document.body.style.overflow = 'hidden';
            navigator.vibrate(35);
        }
        if (photo.alt === "video") {
            const videoAlt = encodeURIComponent(photo.id); // Кодируем alt для URL
            const videoUrl = `https://example.com/mediaS3.php?y=${year}&c=${category}&media=${videoAlt}`;
            videoboxSource.src = videoUrl;
            videoboxSource.type = "video/mp4"; // Убедитесь, что тип правильный
            videobox.load(); // Перезагружаем видео
            videobox.classList.add("show");
            videoboxcontainer.style.display = "flex";
            navigator.vibrate(35);

            // Уведомление о загрузке
            notification.style.animation = 'none';
            void notification.offsetWidth; // Принудительное обновление DOM
            notification.style.animation = 'pulse 2s ease-in-out';
            notification.classList.remove("hidden");
            notification.innerHTML = "Идёт загрузка..<br/>Не нажимайте снова!";

            function hideNotification() {
                notification.classList.add("hidden");
            }
            window.notificationTimer = setTimeout(hideNotification, 5000);
        }
    });
});