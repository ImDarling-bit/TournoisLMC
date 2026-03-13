<?php
// me.php - Retourne les informations de l'utilisateur connecte
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// $payload est defini par index.php apres verification du JWT
if (!isset($payload) || !isset($payload['sub'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->prepare('SELECT id, email, name FROM `USER` WHERE id = :id');
    $stmt->execute([':id' => $payload['sub']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $user['id'] = (int)$user['id'];

    http_response_code(200);
    echo json_encode(['data' => $user]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
}
