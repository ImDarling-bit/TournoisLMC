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
// $idT = ID tournoi (depuis index.php)
// $idR = ID round (depuis index.php)

// Creer un round
if ($method === 'POST') {
    if (!$idT) {
        http_response_code(400);
        echo json_encode(['error' => 'Tournament ID is required']);
        exit;
    }

    $data = getJsonInput();
    $name = isset($data['name']) ? trim($data['name']) : null;

    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'name is required']);
        exit;
    }

    try {
        // Verifier que le tournoi existe
        $stmt = $pdo->prepare('SELECT id FROM `TOURNAMENT` WHERE id = :idT');
        $stmt->execute([':idT' => $idT]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Tournament not found']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO `ROUND` (name, idT) VALUES (:name, :idT)');
        $stmt->execute([':name' => $name, ':idT' => $idT]);
        $newId = $pdo->lastInsertId();

        http_response_code(201);
        echo json_encode([
            'data' => [
                'id' => (int)$newId,
                'name' => $name,
                'idT' => (int)$idT
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Supprimer un round
if ($method === 'DELETE') {
    if (!$idR) {
        http_response_code(400);
        echo json_encode(['error' => 'Round ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM `ROUND` WHERE id = :id');
        $stmt->execute([':id' => $idR]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Round not found']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Round deleted']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Modifier un round
if ($method === 'PUT') {
    if (!$idR) {
        http_response_code(400);
        echo json_encode(['error' => 'Round ID is required']);
        exit;
    }

    $data = getJsonInput();
    $name = isset($data['name']) ? trim($data['name']) : null;

    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'name is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE `ROUND` SET name = :name WHERE id = :id');
        $stmt->execute([':name' => $name, ':id' => $idR]);

        if ($stmt->rowCount() === 0) {
            // Verifier si le round existe
            $check = $pdo->prepare('SELECT id FROM `ROUND` WHERE id = :id');
            $check->execute([':id' => $idR]);
            if (!$check->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Round not found']);
                exit;
            }
        }

        echo json_encode([
            'data' => [
                'id' => (int)$idR,
                'name' => $name
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Recuperer les rounds
if ($method === 'GET') {
    try {
        // Si idR est defini, on recupere un round specifique
        if (isset($idR) && $idR) {
            $stmt = $pdo->prepare('SELECT id, name, idT FROM `ROUND` WHERE id = :id');
            $stmt->execute([':id' => $idR]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                http_response_code(404);
                echo json_encode(['error' => 'Round not found']);
            } else {
                $row['id'] = (int)$row['id'];
                $row['idT'] = (int)$row['idT'];
                echo json_encode(['data' => $row]);
            }
        }
        // Si idT est defini, on recupere les rounds d'un tournoi
        elseif (isset($idT) && $idT) {
            $stmt = $pdo->prepare('SELECT id, name, idT FROM `ROUND` WHERE idT = :idT ORDER BY id');
            $stmt->execute([':idT' => $idT]);
            $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir les IDs en entiers
            foreach ($all as &$row) {
                $row['id'] = (int)$row['id'];
                $row['idT'] = (int)$row['idT'];
            }

            echo json_encode(['data' => $all]);
        }
        // Sinon, on recupere tous les rounds
        else {
            $stmt = $pdo->query('SELECT id, name, idT FROM `ROUND` ORDER BY idT, id');
            $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($all as &$row) {
                $row['id'] = (int)$row['id'];
                $row['idT'] = (int)$row['idT'];
            }

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
