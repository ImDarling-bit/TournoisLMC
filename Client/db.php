<?php
// Client/db.php - Connexion BDD via Client/config.php (indépendant de l'API)
$config = require __DIR__ . '/config.php';
$dbConf = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$dbConf['host']};dbname={$dbConf['name']};charset={$dbConf['charset']}",
        $dbConf['user'],
        $dbConf['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("Erreur de connexion BDD : " . $e->getMessage());
}
