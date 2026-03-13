<?php
// token.php - gère l'octroi de type Mot de Passe Propriétaire de la Ressource
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
// utiliser $_POST en repli
$input = array_merge($_POST, $input);

$email = $input['email'] ?? null;
$password = $input['password'] ?? null;

if ( !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request', 'error_description' => 'email/password required']);
    exit;
}

/* Déjà inclus dans index.php
require __DIR__ . '/db.php';
$config = require __DIR__ . '/config.php';
require __DIR__ . '/jwt.php';*/

// rechercher l'utilisateur
$stmt = $pdo->prepare('SELECT id, email, pass FROM user WHERE email = :email');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['pass'])) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid_grant', 'error_description' => 'invalid credentials']);
    exit;
}

$now = time();
$exp = $now + ($config['access_token_lifetime'] ?? 3600);
$payload = [
    'iss' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'iat' => $now,
    'exp' => $exp,
    'sub' => $user['id'],
    'email' => $user['email'],
];

$token = jwt_encode($payload, $config['jwt_secret']);

$response = [
    'access_token' => $token,
    'token_type' => 'Bearer',
    'expires_in' => $exp - $now,
];

echo json_encode($response);
