<?php
    $bots = [
    'facebookexternalhit', // Facebook
    'Twitterbot',          // Twitter
    'WhatsApp',            // WhatsApp
    'TelegramBot',         // Telegram
    'Viber',               // Viber
    'Discordbot',          // Discord
    'Googlebot',           // Google
    'Bingbot',             // Bing
    'Slackbot',            // Slack
    'LinkedInBot',         // LinkedIn
    ];
    
    // Проверяем, является ли пользователь ботом
    $isBot = false;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    foreach ($bots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            $isBot = true;
            break;
        }
    }
    
    // Если это бот, перенаправляем на meta.php
    if ($isBot) {
        header('Location: /../meta.php');
        exit();
    }    

    session_start();
    session_regenerate_id(true);

    // Хэшируем user-agent браузера для последующей проверки
    $userAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

    // Проверяем наличие авторизационной сессии
    if (!$_SESSION['auth']) {
        $_SESSION['act'] = "<b>⛔ Отсутствие сессии:</b>";

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if ($uri !== '/') {
            require 'defines.php';
            $redirectURL = SSL . DOMAIN . $uri;
            $redirectRL = $uri;
            // Сохраняем url к которому хотел подключится пользователь, для дальнейшего предложения о возвращении на него
            $_SESSION['redirect']['URL'] = $redirectURL;
            $_SESSION['redirect']['RL'] = $redirectRL;
        }

        require 'log.php';
        header('Location: /../auth.php');
        exit();
    }

    // Проверка флага запрета доступа
    if ($_SESSION['NOauth']) {
        $_SESSION['act'] = "<b>❌ Доступ запрещен:</b>";
        require 'log.php';
        header('Location: /../non.php');
        exit();
    }

    // Проверка обязательных параметров в сессии
    $requiredSessionKeys = [
        'data',
        'serves',
        'agent',
    ];
    foreach ($requiredSessionKeys as $key) {
        if (!isset($_SESSION['auth'][$key])) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['act'] = "<b>❗ Отсутствие параметра <i><u>$key</u></i> в сессии:</b>";
            require 'log.php';
            header('Location: ../auth.php');
            exit;
        }
    }

    // Проверка целостности user-agent, для защиты от перехвата сессии
    if ($_SESSION['auth']['agent'] !== $userAgentHash) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['act'] = "<b>❗ ❌ Попытка перехвата сессии:</b>\nНеверный user-agent";
        require 'log.php';
        header('Location: ../auth.php');
        exit;
    }
    
    // Лог успешного подключения
    $_SESSION['act'] = "<b>✔️ Подключился:</b>";
    require 'log.php';
?>