<?php
$config = require __DIR__ . '/config.php';
$DB_HOST = $config['db']['host'];
$DB_NAME = $config['db']['name'];
$DB_USER = $config['db']['user'];
$DB_PASS = $config['db']['pass'];
$DB_CHARSET = $config['db']['charset'];

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=" . $DB_CHARSET, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8;');
    echo json_encode(['error' => 'DB connection failed', 'message' => $e->getMessage()]);
    exit;
}

// $pdo est disponible pour les scripts qui incluent ce fichier
