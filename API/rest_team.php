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
// $idTeam récupéré dans index.php

// ajouter une équipe
if ($method === 'POST') {
    $data = getJsonInput();
    $name = isset($data['name']) ? trim($data['name']) : null;

    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'name is required']);
        exit;
    }

    try {
        // nom, et id auto increment
        $stmt = $pdo->prepare('INSERT INTO team (name, idT) VALUES (:name, :idT)');
        $stmt->execute([':name' => $name, ':idT' => $idT]);
        $newId = $pdo->lastInsertId();

        http_response_code(201);
         
        echo json_encode([
            'data' => [
                'id' => (int)$newId,
                'name' => $name,
                'points' => 0 
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// suppression d'une équipe
if ($method === 'DELETE') {
    if (!$idTeam) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required']);
        exit;
    }

    try {
        // verification nom ou id
        $stmt = $pdo->prepare('DELETE FROM team WHERE id = :id');
        $stmt->execute([':id' => $idTeam]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Team not found']);
        } else {
            echo json_encode(['success' => true]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// put (modif)
if ($method === 'PUT') {
    $data = getJsonInput();
    $name = isset($data['name']) ? trim($data['name']) : null;

    if (!$idTeam || !$name) {
        http_response_code(400);
        echo json_encode(['error' => 'ID and name are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE team SET name = :name WHERE id = :id');
        $stmt->execute([':name' => $name, ':id' => $idTeam]);
        
        // Je renvoie les données mises à jour
        echo json_encode(['data' => ['id' => $idTeam, 'name' => $name]]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// get
if ($method === 'GET') {
    try {
        if (isset($idTeam)) {
            $stmt = $pdo->prepare('SELECT id, name, points FROM team WHERE id = :id');
            $stmt->execute([':id' => $idTeam]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(404);
                echo json_encode(['error' => 'Team not found']);
            } else {
                echo json_encode(['data' => $row]);
            }
        } else {
            
            $stmt = $pdo->query('SELECT id, name, points FROM team ORDER BY id');
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