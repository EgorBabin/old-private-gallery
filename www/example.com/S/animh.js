// Отслеживает клик по кнопке «домой» на странице photos.php
// При клике скрывает элементы .gallery и .info с задержкой для плавного эффекта

document.querySelector('.gohome').addEventListener('click', function() {
    const gall = document.querySelector(".gallery");
    const info = document.querySelector(".info");
    setTimeout(() => {
        gall.style.display = 'none';
        info.style.display = 'none';
    }, 300);
});