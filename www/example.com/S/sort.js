// Добавляет переключатель сортировки в photos.php
// позволяет выбирать порядок отображения фото (старое->новое (1-2-3)/ новое->старое (3-2-1))
document.addEventListener('DOMContentLoaded', function() {
    // Элементы управления
    const sortToggle = document.getElementById('sort-toggle');
    const gallery = document.querySelector('.gallery');
    
    // Проверка поддержки cookies
    if (!navigator.cookieEnabled) {
        sortToggle.parentElement.textContent = "Сортировка недоступна (требуются cookies)";
        return;
    }

    // Получения cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Установить cookie с указанием срока (дней)
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
    }

    // Сортировка фото по id (числовому)
    function sortGallery(order) {
        const photos = Array.from(gallery.querySelectorAll('.photo'));
        
        photos.sort((a, b) => {
            const idA = parseInt(a.id, 10);
            const idB = parseInt(b.id, 10);
            return order === 'asc' ? idA - idB : idB - idA;
        });

        // Перерисовываем галерею
        gallery.innerHTML = '';
        photos.forEach(photo => gallery.appendChild(photo));
    }

    // Инициализация состояния
    let currentSort = getCookie('photo_sort') || 'asc';
    updateSortDisplay();

    // Переключение сортировки по клику
    sortToggle.addEventListener('click', function(e) {
        e.preventDefault();
        currentSort = currentSort === 'asc' ? 'desc' : 'asc';
        setCookie('photo_sort', currentSort, 30);
        sortGallery(currentSort);
        updateSortDisplay();
    });

    // Обновление текста кнопки и сортировка галереи
    function updateSortDisplay() {
        sortToggle.textContent = currentSort === 'asc' 
            ? 'старое - новое' 
            : 'новое - старое';
        sortGallery(currentSort);
    }
});