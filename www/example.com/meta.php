<?php
    // Страница для ботов - соцсетей, чтобы присылать красивые ссылки)
    require 'defines.php';

    // Устанавливаем заголовок для правильного отображения в соцсетях
    header('Content-Type: text/html; charset=utf-8');

    // Метатеги Open Graph
    $title = SITE_NAME . " — " . SITE_SLOGAN;
    $description = SITE_DESCRIPTION_META;
    $image = SSL . DOMAIN . "/img/ico.png";
    $url = DOMAIN;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($image); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($url); ?>">
    <meta property="og:type" content="website">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
    <!-- Пустое тело, так как контент не нужен -->
</body>
</html>