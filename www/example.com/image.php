<?php
    require 'S/checkauth.php';
    require_once '/var/www/user/data/vendor/autoload.php';
    // Поменяйте на свои пути, если отличаются

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

    // Получаем путь к изображению из запроса
    $imagePath = $_GET['path'] ?? '';

    if (empty($imagePath)) {
        http_response_code(400);
        exit("Ошибка: не указан путь к изображению.");
    }

    $bucketName = $config['AWS_BUCKET'];

    try {
        // Загружаем объект из S3
        $result = $s3Client->getObject([
            'Bucket' => $bucketName,
            'Key'    => $imagePath,
        ]);

        // Получаем MIME-тип и размер файла из ответа S3
        $contentType = $result['ContentType'] ?? 'image/jpeg';
        $contentLength = $result['ContentLength'] ?? 0;

        // Отправляем заголовки
        header("Content-Type: $contentType");
        header("Content-Length: " . $contentLength);
        header("Cache-Control: public, max-age=36000"); // Кеш на 10 часов

        // Если тело объекта является потоком, выводим его через fpassthru
        if (is_object($result['Body']) && method_exists($result['Body'], 'detach')) {
            $stream = $result['Body']->detach();
            fpassthru($stream);
        } else {
            echo $result['Body'];
        }
        exit;
    } catch (AwsException $e) {
        http_response_code(404); // если не найдено - выводим картинку
        header('Content-Type: image/svg+xml');
        readfile('ico/404img.svg');
        exit;
    }
?>