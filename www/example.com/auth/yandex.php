<?php
    session_start();

    $config = require('../../config.php');
    require '../defines.php';

    // Ваши данные от Яндекс OAuth
    $clientId = $config['clientId'];
    $clientSecret = $config['clientSecret'];
    $redirectUri = SSL . DOMAIN . '/auth/yandex.php';

    $serves = 'Yandex';
    $userAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

    // Генерация CSRF-токена, если его нет
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Проверка CSRF-токена, если это POST-запрос
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Ошибка CSRF: недействительный токен.');
        }
        // Удаляем CSRF-токен после использования
        unset($_SESSION['csrf_token']);
    }

    // Если код авторизации не получен, перенаправляем пользователя на Яндекс для авторизации
    if (!isset($_GET['code'])) {
        $authUrl = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $clientId . '&redirect_uri=' . urlencode($redirectUri);
        header('Location: ' . $authUrl);
        exit;
    }

    // Если код авторизации получен, запрашиваем access_token
    $code = $_GET['code'];
    $tokenUrl = 'https://oauth.yandex.ru/token';
    $postData = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);

    if (isset($tokenData['access_token'])) {
        // Получаем информацию о пользователе
        $userInfoUrl = 'https://login.yandex.ru/info?format=json&oauth_token=' . $tokenData['access_token'];
        $userInfo = json_decode(file_get_contents($userInfoUrl), true);
    
        // Проверяем, есть ли email и телефон в ответе
        $email = $userInfo['default_email'] ?? null;
        $phone = $userInfo['default_phone']['number'] ?? null;
    
        if ($email || $phone) {
            // Проверяем, есть ли email или телефон в списке разрешенных
            $allowedData = include '../users.php';
    
            if (in_array($email, $allowedData) || in_array($phone, $allowedData)) {
                // Пользователь авторизован
                $_SESSION['auth']['data'] = $email ?? $phone; // Сохраняем email или телефон
                $_SESSION['auth']['serves'] = $serves; // Указываем сервис
                $_SESSION['auth']['agent'] = $userAgentHash;
                $_SESSION['act'] = "<b>✅ Пользователь авторизовался:</b> ";
                require '../S/log.php';
                header('Location: ..');
                exit;
            } else {
                // Пользователь не авторизован
                $_SESSION['NOauth']['NOdata'] = $email ?? $phone;
                $_SESSION['NOauth']['NOserves'] = $serves;
                $_SESSION['act'] = "<b>❌ Доступ запрещен:</b> ";
                require '../S/log.php';
                header('Location: ../non.php');
                exit;
            }
        } else {
            // Если email и телефон отсутствуют в ответе
            $_SESSION['NOauth']['NOserves'] = $serves;
            $_SESSION['act'] = "<b>❌ Что-то пошло не так:</b> Email и телефон не получены.";
            require '../S/log.php';
            header('Location: ../non.php');
            exit;
        }
    } else {
        // Если access_token не получен
        $_SESSION['NOauth']['NOserves'] = $serves;
        $_SESSION['act'] = "<b>❌ Что-то пошло не так:</b> Не удалось получить access_token.";
        require '../S/log.php';
        header('Location: ../non.php');
        exit;
    }
?>