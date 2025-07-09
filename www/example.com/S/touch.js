// Позволяет свайпом вверх скрывать открытую фотографию - lightbox, в photos.php

let touchStartY = 0; // Начальная позиция касания
let touchEndY = 0; // Текущая позиция касания
let isSwipingUp = false; // Флаг для отслеживания свайпа вверх
let minDistance = 0; // Минимальное расстояние свайпа вверх
let hasReversed = false; // Флаг для отслеживания возврата пальца

lightbox.addEventListener("touchstart", (e) => {
    touchStartY = e.touches[0].clientY; // Запоминаем начальную позицию
    lightbox.style.transition = "none"; // Отключаем плавность для мгновенного движения
    isSwipingUp = false; // Сбрасываем флаг свайпа вверх
    hasReversed = false; // Сбрасываем флаг возврата
    minDistance = 0; // Сбрасываем минимальное расстояние
});

lightbox.addEventListener("touchmove", (e) => {
    if (e.touches.length === 1) { // Работаем только с одиночным касанием
        touchEndY = e.touches[0].clientY; // Текущая позиция пальца
        const moveDistance = touchStartY - touchEndY; // Расстояние свайпа вверх

        // Определяем направление свайпа
        if (moveDistance > 0 && !isSwipingUp) {
            isSwipingUp = true;
        }

        // Обновляем минимальное расстояние свайпа вверх
        if (isSwipingUp && moveDistance > minDistance) {
            minDistance = moveDistance;
        }

        // Если пользователь начал возврат вниз на 20px или больше
        if (isSwipingUp && (minDistance - moveDistance) >= 20) {
            hasReversed = true;
        }

        // Применяем трансформацию в зависимости от направления
        if (isSwipingUp) {
            if (!hasReversed) {
                // Если возврата не было, двигаем лайтбокс вверх
                lightbox.style.transform = `translateY(${-moveDistance}px)`;
                lightbox.style.opacity = 1 - Math.abs(moveDistance) / 300;
            } else {
                // Если был возврат, двигаем лайтбокс вниз
                const reverseDistance = minDistance - moveDistance;
                lightbox.style.transform = `translateY(${-minDistance + reverseDistance}px)`;
                lightbox.style.opacity = 1 - Math.abs(minDistance - reverseDistance) / 300;
            }
        }

        e.preventDefault(); // Блокируем прокрутку страницы
    }
});

lightbox.addEventListener("touchend", () => {
    const moveDistance = touchStartY - touchEndY; // Общее расстояние свайпа вверх

    // Закрываем лайтбокс только если:
    // 1. Свайп был вверх (isSwipingUp === true)
    // 2. Не было возврата (hasReversed === false)
    // 3. Расстояние свайпа больше 80px
    if (isSwipingUp && !hasReversed && moveDistance > 80) {
        closeLightbox();
    } else {
        // Возвращаем лайтбокс в исходное положение
        lightbox.style.transition = "transform 0.2s, opacity 0.2s";
        lightbox.style.transform = "translateY(0)";
        lightbox.style.opacity = 1;
    }
});

// Закрытие по прокрутке колесика мыши
lightbox.addEventListener("wheel", (e) => {
    if (e.deltaY > 50 || e.deltaY < -50) {
        closeLightbox();
    }
});

function closeLightbox() {
    lightbox.classList.remove("show");
    lightbox.style.transition = "transform 0.2s, opacity 0.2s";
    lightbox.style.transform = "translateY(0)";
    lightbox.style.opacity = 1;
    lightboxImg.src = "";
    document.body.style.overflow = "auto";
}