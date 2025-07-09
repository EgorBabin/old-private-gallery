<?php
    session_start();
    // Проверяем есть ли url на который хотел зайти пользователь
    // Есть - показываем кнопку с предложением вернуться на него в index.php
    // Если нет, просто не показываем
    if($_SESSION['redirect']) {
        $redirectURL = htmlspecialchars($_SESSION['redirect']['URL'], ENT_QUOTES, 'UTF-8');
        $redirectRL = htmlspecialchars($_SESSION['redirect']['RL'], ENT_QUOTES, 'UTF-8');
        echo "<a class='btn' href='$redirectURL' onclick='navigator.vibrate(50);'>Вернуться на:<br>$redirectRL</a>";
        unset($_SESSION['redirect']);
    }
?>