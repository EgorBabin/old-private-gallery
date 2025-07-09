// Управление видео и уведомлением на странице
document.addEventListener("DOMContentLoaded", function () {
    const video = document.getElementById("videobox");
    const notification = document.getElementById("notification");

    // Показываем видео и уведомление о готовности к просмотру
    video.classList.add("show");

    // Уведомление о загрузке видео
    video.addEventListener("canplay", function () {
        if (window.notificationTimer) {
            clearTimeout(window.notificationTimer);
        }
        void notification.offsetWidth; // Принудительное обновление DOM
        navigator.vibrate(35);
        notification.style.animation = 'pulse 2s ease-in-out';
        notification.classList.remove("hidden");
        notification.innerHTML = "Нажмите для просмотра видео";
        if (notification && video) {
            // Флаг для отслеживания, было ли уведомление скрыто
            let isNotificationHidden = false;
        
            // Скрываем уведомление, когда видео видимо на экране
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.intersectionRatio >= 1 && !isNotificationHidden) {
                            notification.classList.add("hidden");
                            isNotificationHidden = true;
                        }
                    });
                },
                {
                    threshold: 1,
                }
            );
        
            // Начинаем наблюдение за элементом
            observer.observe(video);
        }
    });

    // Обработка ошибок загрузки видео с уведомлением и вибрацией
    video.addEventListener("error", function () {
        if (window.notificationTimer) {
            clearTimeout(window.notificationTimer);
        }
        notification.style.animation = 'none';
        void notification.offsetWidth; // Принудительное обновление DOM
        navigator.vibrate(35);
        notification.style.animation = 'pulse 2s ease-in-out';
        notification.classList.remove("hidden");
        notification.textContent = "Ошибка при загрузке видео.";
    });

    // По клику на уведомление скроллим к видео и скрываем уведомление
    notification.addEventListener("click", function () {
        video.scrollIntoView();
        notification.classList.add("hidden");
    });
});

// Добавляем SVG-иконку "play" поверх изображений с alt="video"
document.addEventListener('DOMContentLoaded', () => {
    const svgIcon = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="50px" height="50px">
        <defs>
            <linearGradient id="myGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="var(--color-start)" /> <!-- Начальный цвет -->
            <stop offset="100%" stop-color="var(--color-end)" /> <!-- Конечный цвет -->
            </linearGradient>
        </defs>

        <path d="M8 5v14l11-7z" fill="url(#myGradient)" />
        </svg>
    `;

    const images = document.querySelectorAll('img');
    images.forEach(img => {
        if (img.alt === 'video') {
            const container = document.createElement('div');
            container.classList.add('image-container');
            img.insertAdjacentElement('beforebegin', container);
            container.appendChild(img);

            const overlay = document.createElement('div');
            overlay.classList.add('overlay');
            overlay.innerHTML = svgIcon;

            container.appendChild(overlay);
        }
    });
});