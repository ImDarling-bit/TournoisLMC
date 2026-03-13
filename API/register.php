<?php
// register.php - Inscription d'un nouvel utilisateur
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'invalid_request', 'error_description' => 'POST required']);
    exit;
}

// Accepter JSON ou corps encodé en formulaire
$body = file_get_contents('php://input');
$input = [];
if (!empty($body)) {
    $decoded = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) $input = $decoded;
}
$input = array_merge($_POST, $input);

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$name = trim($input['name'] ?? '');

// Validation
if (!$email || !$password || !$name) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request', 'error_description' => 'name, email et password requis']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request', 'error_description' => 'Email invalide']);
    exit;
}

require __DIR__ . '/db.php';

// Verifier si email existe deja
$stmt = $pdo->prepare('SELECT id FROM `USER` WHERE email = :email');
$stmt->execute([':email' => $email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'conflict', 'error_description' => 'Email deja utilise']);
    exit;
}

// Hasher le mot de passe
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Inserer l'utilisateur
$stmt = $pdo->prepare('INSERT INTO `USER` (email, name, pass) VALUES (:email, :name, :pass)');
$success = $stmt->execute([
    ':email' => $email,
    ':name' => $name,
    ':pass' => $hashedPassword
]);

if ($success) {
    $userId = $pdo->lastInsertId();
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur cree',
        'user' => [
            'id' => (int)$userId,
            'email' => $email,
            'name' => $name
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'error_description' => 'Erreur lors de la creation']);
}
