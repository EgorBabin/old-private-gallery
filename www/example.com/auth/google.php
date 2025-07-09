<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once '/var/www/user/data/vendor/autoload.php'; // Подключаем автозагрузчик

    require '../defines.php';

    // Запускаем сессию
    session_start();

    // Если пользователь уже авторизован, перенаправляем на главную
    if (isset($_SESSION['auth'])) {
        header('Location: ..');
        exit();
    }

    $serves = 'Google';
    $userAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

    $client = new Google\Client();
    $client->setAuthConfig('../../client_secret_.apps.googleusercontent.com.json'); // Путь к credentials.json
    $client->setRedirectUri(SSL .DOMAIN .'/auth/google.php'); // Ваш Redirect URI
    $client->addScope(Google\Service\Oauth2::USERINFO_EMAIL);
    $client->addScope(Google\Service\Oauth2::USERINFO_PROFILE);

    if (isset($_GET['code'])) {
        $_SESSION['NOauth']['NOserves'] = $serves;
        $_SESSION['act'] = "<b>📬 Код авторизации получен:</b>";
        require '../S/log.php';
        echo "Authorization code received.\n"; // Отладочный вывод
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        $oauth = new Google\Service\Oauth2($client);
        $userInfo = $oauth->userinfo->get();

        $email = $userInfo->email;
        echo "User email: $email\n"; // Отладочный вывод

        $allowedEmails = include '../users.php';

        if (in_array($email, $allowedEmails)) {
            $_SESSION['auth']['data'] = $email;
            $_SESSION['auth']['serves'] = $serves;
            $_SESSION['auth']['agent'] = $userAgentHash;
            $_SESSION['act'] = "<b>✅ Пользователь авторизовался:</b>";
            require '../S/log.php';
            echo "User authorized. Redirecting to index.php.\n"; // Отладочный вывод
            header('Location: ..');
            exit();
        } else {
            $_SESSION['NOauth']['NOdata'] = $email;
            $_SESSION['NOauth']['NOserves'] = $serves;
            $_SESSION['act'] = "<b>❌ Доступ запрещен:</b>";
            require '../S/log.php';
            echo "User not authorized. Redirecting to non.php.\n"; // Отладочный вывод
            header('Location: ../non.php');
            exit();
        }
    } else {
        $_SESSION['NOauth']['NOserves'] = $serves;
        $_SESSION['act'] = "<b>📭 Нет кода авторизации:</b>";
        require '../S/log.php';
        echo "No authorization code. Redirecting to Google OAuth.\n"; // Отладочный вывод
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit();
    }
?>