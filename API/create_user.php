<?php
// create_user.php
// Usage CLI: php create_user.php email nom motdepasse

if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method Not Allowed');
}

require __DIR__ . '/db.php';

// Récupération
if (php_sapi_name() === 'cli') {
    $argv = $_SERVER['argv'];
    if (count($argv) < 4) {
        echo "Usage: php create_user.php email name password\n";
        exit(1);
    }
    $email = $argv[1];
    $name = $argv[2];
    $pass = $argv[3];
} else {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    $email = $data['email'] ?? null;
    $name = $data['name'] ?? null;
    $pass = $data['pass'] ?? null;
}

if (!$email || !$name || !$pass) {
    if (php_sapi_name() === 'cli') echo "Erreur: champs manquants\n";
    else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
    }
    exit(1);
}

$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

try {
    // Insertion sans ID
    $stmt = $pdo->prepare('INSERT INTO user (email, name, pass) VALUES (:email, :name, :pass)');
    $stmt->execute([':email' => $email, ':name' => $name, ':pass' => $hashedPass]);
    
    // Récupération ID
    $newId = $pdo->lastInsertId();

    if (php_sapi_name() === 'cli') {
        echo "Succès : User $name créé avec l'ID $newId.\n";
    } else {
        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $newId]);
    }

} catch (PDOException $e) {
    if (php_sapi_name() === 'cli') echo "Erreur: " . $e->getMessage() . "\n";
    else {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}