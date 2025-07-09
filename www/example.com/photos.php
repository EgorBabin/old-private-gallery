<?php
// Кидает при клике на карточку с index.php
// Выводит витрину изображений и видео
require 'S/checkauth.php';
require_once '/var/www/user/data/vendor/autoload.php'; // Измените путь, если отличается

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$config = require('../config.php');

$s3Client = new S3Client([
    'region'  => 'region',
    'version' => 'latest',
    'endpoint' => $config['AWS_ENDPOINT'],
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => $config['AWS_ACCESS_KEY'],
        'secret' => $config['AWS_SECRET_KEY'],
    ]
]);

// Получаем и фильтруем параметры из URL
$year = isset($_GET['y']) ? preg_replace('/[^0-9]/', '', $_GET['y']) : '';
$category = isset($_GET['c']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['c']) : '';
if (!$year || !$category) {
    exit("<p>Не указаны параметры year и category.</p>");
}

// Читаем JSON-файл
$jsonString = file_get_contents('cards.json');
$data = json_decode($jsonString, true);

if ($data === null) {
    die("Ошибка чтения JSON файла");
}

$foundData = null;
foreach ($data as $item) {
    if (isset($item['year']) && 
        isset($item['category']) && 
        $item['year'] == $year && 
        $item['category'] == $category) {
        $foundData = $item;
        break;
    }
}

if ($foundData) {
    $title = $foundData['title'] ?? null;
}

// Формируем префикс для поиска в бакете
$prefix = "p/$year/$category/";

// Получаем список объектов
try {
    $listResult = $s3Client->listObjects([
        'Bucket' => $config['AWS_BUCKET'],
        'Prefix' => $prefix,
        'Delimiter' => '/',  // Ограничивает выборку объектами, находящимися непосредственно в этой папке
    ]);
} catch (AwsException $e) {
    exit("<p>Ошибка получения списка изображений из S3: " . $e->getMessage() . "</p>");
}

if (!isset($listResult['Contents']) || count($listResult['Contents']) == 0) {
    exit("<p>Изображения в этой категории отсутствуют.</p>");
}

// Отбираем только изображения
$images = [];
foreach ($listResult['Contents'] as $object) {
    $key = (string)$object['Key'];
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $key)) {
        $images[] = $key;
    }
}
if (empty($images)) {
    exit("<p>Изображения в этой категории отсутствуют.</p>");
}

// Сортируем по числам в имени файла
usort($images, function($a, $b) {
    return preg_replace('/\D/', '', $a) <=> preg_replace('/\D/', '', $b);
});

// Время действия ссылки (например, 60 sec)
$urlExpiry = '+60 seconds';

?><!DOCTYPE html>
<html lang="ru">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>site name — <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'S/progress-bar.html'; ?>

    <button onclick="window.location.href = '..'; navigator.vibrate(35)" class="gohome" title="На главную">
        <img src="ico/menu.svg" class="icotext">
    </button>
    <b><p class="info"><?php echo "$year $title";?></p></b><!--Вывод года и названия-->
    <div class="sort-block"><!--Выборка сортировки-->
        Сортировка: <a href="#" id="sort-toggle"></a>
    </div>
    <script src="S/sort.js"></script>

    <!--Витрина изображений-->
    <div class="gallery">
        <?php foreach ($images as $objectKey): 
            // Определяем тип и номер по имени файла (предполагается, что имя содержит цифры)
            if (preg_match('/v(\d+)\.jpg$/i', $objectKey, $matches)) {
                $typeid = "video";
                $number = $matches[1];
            } elseif (preg_match('/(\d+)\.jpg$/i', $objectKey, $matches)) {
                $typeid = "image";
                $number = $matches[1];
            } else {
                $typeid = "image";
                $number = '';
            }
            
            // Генерируем подписанную ссылку для объекта
            try {
                $cmd = $s3Client->getCommand('GetObject', [
                    'Bucket' => $config['AWS_BUCKET'],
                    'Key'    => $objectKey
                ]);
                $request = $s3Client->createPresignedRequest($cmd, $urlExpiry);
                $signedUrl = (string)$request->getUri();
            } catch (AwsException $e) {
                // Если возникает ошибка при генерации ссылки, пропускаем изображение
                continue;
            }
        ?>
            <img class="photo" src="<?php echo htmlspecialchars($signedUrl); ?>" id="<?php echo htmlspecialchars($number); ?>" alt="<?php echo htmlspecialchars($typeid); ?>">
        <?php endforeach; ?>
    </div>

    <!--Контейнер для "оригинального" изображения - на весь экран-->
    <div class="lightbox" id="lightbox">
        <img src="" alt="">
    </div>

    <!--Контейнер для вывода видео-->
    <div class="videobox-container">
        <video class="videobox" id="videobox" controls width="90%">
            <source src="" type="video/mp4">
            Ваш браузер не поддерживает видео.
        </video>
    </div>

    <!--Уведомление для видео-->
    <div id="notification" class="notification hidden">
        Видео загружено!Нажмите, чтобы перейти к видео.
    </div>

    <script src="S/skelet.js"></script>

    <script src="S/video.js"></script>

    <script src="S/display.js"></script>

    <script src="S/touch.js"></script>

    <script src="S/animh.js"></script>
</body>
</html>