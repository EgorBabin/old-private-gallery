// Отслеживает клик по контейнеру с привью фотографией на странице index.php
// При клике скрывает элементы .container с задержкой для плавного эффекта

document.querySelector('.container').addEventListener('click', function() {
    const cont = document.querySelector(".container");
    cont.classList.add("anim");
    setTimeout(() => {
        cont.style.display = 'none';
    }, 300);
});