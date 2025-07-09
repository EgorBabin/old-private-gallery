<?php
require 'S/checkauth.php';
require_once '/var/www/user/data/vendor/autoload.php'; // Замените на свой путь, если отличается

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
$year = htmlspecialchars($_GET['y'] ?? '');
$category = htmlspecialchars($_GET['c'] ?? '');
$media = htmlspecialchars($_GET['media'] ?? '');
if (empty($year) || empty($category) || empty($media)) {
    http_response_code(400);
    exit("Ошибка: не указаны необходимые параметры.");
}

// Формируем S3-ключ для файла (предполагается, что видео находится в папке video)
$objectKey = "p/$year/$category/video/{$media}.mp4";
$bucketName = $config['AWS_BUCKET'];

// Получаем метаданные файла через headObject
try {
    $headResult = $s3Client->headObject([
        'Bucket' => $bucketName,
        'Key'    => $objectKey,
    ]);
} catch (AwsException $e) {
    http_response_code(404);
    exit("Media file not found.");
}

$mimeType = $headResult['ContentType'] ?? 'application/octet-stream';
$fileSize = $headResult['ContentLength'] ?? 0;

if (strpos($mimeType, 'image') === 0) {
    // Для изображений отдаем весь файл
    try {
        $result = $s3Client->getObject([
            'Bucket' => $bucketName,
            'Key'    => $objectKey,
        ]);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $fileSize);
        header("Cache-Control: public, max-age=36000");
        if (is_object($result['Body']) && method_exists($result['Body'], 'detach')) {
            $stream = $result['Body']->detach();
            fpassthru($stream);
        } else {
            echo $result['Body'];
        }
        exit;
    } catch (AwsException $e) {
        http_response_code(404);
        exit("Ошибка при получении изображения.");
    }
} elseif (strpos($mimeType, 'video') === 0) {
    // Для видео поддерживаем Range Requests
    $rangeHeader = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;
    if ($rangeHeader) {
        // Пример Range: "bytes=12345-"
        if (preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches)) {
            $start = intval($matches[1]);
            $end = ($matches[2] !== '') ? intval($matches[2]) : $fileSize - 1;
        } else {
            $start = 0;
            $end = $fileSize - 1;
        }
        if ($start > $end || $end >= $fileSize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes */$fileSize");
            exit;
        }
        $s3Range = "bytes={$start}-{$end}";
        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key'    => $objectKey,
                'Range'  => $s3Range,
            ]);
            $chunkLength = $end - $start + 1;
            header("HTTP/1.1 206 Partial Content");
            header("Accept-Ranges: bytes");
            header("Content-Length: " . $chunkLength);
            header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
            header('Content-Type: ' . $mimeType);
            if (is_object($result['Body']) && method_exists($result['Body'], 'detach')) {
                $stream = $result['Body']->detach();
                fpassthru($stream);
            } else {
                echo $result['Body'];
            }
            exit;
        } catch (AwsException $e) {
            http_response_code(404);
            exit("Ошибка при получении части видео.");
        }
    } else {
        // Если Range не указан - не поддерживается, отдаем весь файл
        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key'    => $objectKey,
            ]);
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . $fileSize);
            if (is_object($result['Body']) && method_exists($result['Body'], 'detach')) {
                $stream = $result['Body']->detach();
                fpassthru($stream);
            } else {
                echo $result['Body'];
            }
            exit;
        } catch (AwsException $e) {
            http_response_code(404);
            exit("Ошибка при получении видео.");
        }
    }
} else {
    http_response_code(415);
    exit("Unsupported media type.");
}
?>