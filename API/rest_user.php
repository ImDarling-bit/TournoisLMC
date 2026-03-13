<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/getJsonInput.php';

if (!isset($secure) || $secure !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Access forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$idFromUrl = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Inscription
if ($method === 'POST') {
    $data = getJsonInput();
    
    // récup des donnés json
    $email = isset($data['email']) ? trim($data['email']) : null;
    $pass = isset($data['password']) ? trim($data['password']) : null; // L'API demande "password"
    $name = isset($data['name']) ? trim($data['name']) : null;

    if (!$email || !$pass) {
        http_response_code(400);
        echo json_encode(['error' => 'email and password are required']);
        exit;
    }

    // hashage du mot de passe
    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

    try {
        // requete sql
        $stmt = $pdo->prepare('INSERT INTO user (email, name, pass) VALUES (:email, :name, :pass)');
        $stmt->execute([':email' => $email, ':name' => $name, ':pass' => $hashedPass]);
        
        $newId = $pdo->lastInsertId();

        http_response_code(201);
        echo json_encode(['message' => 'User created', 'id' => (int)$newId]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
        }
    }
    exit;
}

// suppression
if ($method === 'DELETE') {
    if (!$idFromUrl) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required in URL']);
        exit;
    }
    try {
        $stmt = $pdo->prepare('DELETE FROM user WHERE id = :id');
        $stmt->execute([':id' => $idFromUrl]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404); // 404 = Non trouvé
            echo json_encode(['error' => 'User not found']);
        } else {
            echo json_encode(['message' => 'User deleted']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// changement
if ($method === 'PUT') {
    $data = getJsonInput();
    
    if (!$idFromUrl) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required in URL']);
        exit;
    }

    // mot de passe
    if (isset($data['password'])) {
        $newPass = password_hash($data['password'], PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare('UPDATE user SET pass = :pass WHERE id = :id');
            $stmt->execute([':pass' => $newPass, ':id' => $idFromUrl]);
            echo json_encode(['success' => true, 'message' => 'Password updated']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // infos
    $email = isset($data['email']) ? trim($data['email']) : null;
    $name = isset($data['name']) ? trim($data['name']) : null;

    if ($email || $name) {
        try {
            $sql = "UPDATE user SET ";
            $params = [':id' => $idFromUrl];
            $updates = [];

            if ($email) {
                $updates[] = "email = :email";
                $params[':email'] = $email;
            }
            if ($name) {
                $updates[] = "name = :name";
                $params[':name'] = $name;
            }

            $sql .= implode(", ", $updates) . " WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // envoit des changements
            echo json_encode(['data' => ['id' => $idFromUrl, 'email' => $email, 'name' => $name]]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    http_response_code(400);
    echo json_encode(['error' => 'Nothing to update']);
    exit;
}

// récupérer des ingos
if ($method === 'GET') {
    try {
        if ($idFromUrl) {
        
            $stmt = $pdo->prepare('SELECT id, email, name FROM user WHERE id = :id');
            $stmt->execute([':id' => $idFromUrl]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            } else {

                echo json_encode(['data' => $row]);
            }
        } else {
            // tout les uses
            $stmt = $pdo->query('SELECT id, email, name FROM user ORDER BY name');
            $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['data' => $all]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>