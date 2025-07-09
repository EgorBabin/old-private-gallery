<?php
    // Страница выбора сервиса авторизации
    session_start();
    if ($_SESSION['auth']) {
        $_SESSION['act'] = "<b>⛔ Попытка войти на страницу входа, уже войдя:</b>";
        require 'S/log.php';
        header('Location: ..');
        exit();
    }
    $_SESSION['act'] = "<b>Страница авторизации:</b>";
    require 'S/log.php';
?>

<?php
    require 'defines.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME . " — Авторизация"; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth">
    <div class="auth-container">
        <h1>Выберите сервис для авторизации</h1>
        <a href="https://example.com/auth/google.php" class="auth-button google">
            <img src="ico/google.svg" alt="Google Icon">
            Google
        </a>
        <a href="https://example.com/auth/yandex.php" class="auth-button yandex">
            <img src="ico/yandex.svg" alt="Yandex Icon">
            Yandex
        </a>
    </div>
</body>
</html>