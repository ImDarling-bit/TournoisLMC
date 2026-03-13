<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/getJsonInput.php';

// file_put_contents(__DIR__.'/hdrs.log',$path ."\n". print_r(getallheaders(), true)."\n".print_r($_SERVER, true)."\n", FILE_APPEND);


// Securité a reactiver et a debugger

if (!isset($secure) || $secure !== true) {
        http_response_code(403);
        echo json_encode(['error' => 'Access forbidden']);
        exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// curl -Method POST -Uri "localhost/Aux-Claviers-Citoyens/API/tournements" -Headers $headers -Body '{"name":"test","game":"lol","teamcount":"Inscriptions ouvertes"}' 

if ($method === 'POST') {
    $data = getJsonInput();
    $name = isset($data['name']) ? trim($data['name']) : null;
    $game = isset($data['game']) ? trim($data['game']) : null;
    $TeamCount = isset($data['teamcount']) ? trim($data['teamcount']) : null;
    $idU = isset($data['idU']) ? trim($data['idU']) : $payload["sub"];

    if (!$name || !$game || !$TeamCount) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields name, game or TeamCount']);
        exit;
    }

    if ($TeamCount < 2) {
        http_response_code(400);
        echo json_encode(['error'=> 'TeamCount must be at least 2']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO tournament (name, game, TeamCount, status, idU, DateDeDebut, DateDeFin) VALUES (:name, :game, :TeamCount, :status, :idU, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))');
        $stmt->execute(['name' => $name, 'game' => $game,'TeamCount' => $TeamCount, 'status' => "Inscriptions ouvertes", 'idU' => $idU]);

        http_response_code(201);
        echo json_encode(['id' => $pdo->lastInsertId() , 'name' => $name, 'game' => $game, 'status' => "Inscriptions ouvertes"]);

    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            http_response_code(409);
            echo json_encode(['error' => 'tournament not added']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
        }
    }
    exit;
}

// route localhost/Aux-Claviers-Citoyens/api/tournaments/{tournamentId} pour un tournament spécifique
// et
// route localhost/Aux-Claviers-Citoyens/api/tournaments pour tous les tournaments

if ($method === 'GET') {
    try {
        // $idT est déjà défini par le routing dans index.php
        // $idT sera null pour /tournaments
        // $idT contiendra l'ID pour /tournaments/1
        
        $params = [];
        $conditions = [];
        if ($idT) {
            $conditions[] = 'id = :idT';
            $params[':idT'] = $idT;
        }
        
        // Sélectionner uniquement les champs souhaités
        $sql = 'SELECT id, name, game, status FROM tournament';
        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($tournaments);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}
// curl -Method PUT -Uri "localhost/Aux-Claviers-Citoyens/API/tournaments/3" -Headers $headers -Body '{"name":"World1","game":"lol","status":"En cours"}'
if ($method === 'PUT') {
    $data = getJsonInput();
    $name = isset($data['name']) ? trim($data['name']) : null;
    $game = isset($data['game']) ? trim($data['game']) : null;
    $status = isset($data['status']) ? trim($data['status']) : null;

    if (!$name || !$game || !$status || !$idT) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    try {
        // Mise à jour du tournoi
        $stmt = $pdo->prepare('UPDATE tournament SET name = :name, game = :game, status = :status WHERE id = :id');
        $stmt->execute(['name' => $name, 'game' => $game, 'status' => $status, 'id' => $idT]);
        
        // Récupération du tournoi mis à jour
        $stmt = $pdo->prepare('SELECT id, name, game, status FROM tournament WHERE id = :id');
        $stmt->execute(['id' => $idT]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tournament) {
            http_response_code(404);
            echo json_encode(['error' => 'Tournament not found']);
            exit;
        }
        
        // Conversion de l'id en entier
        $tournament['id'] = (int)$tournament['id'];
        
        http_response_code(200);
        echo json_encode(['data' => $tournament]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}
// curl -Method DELETE -Uri "localhost/Aux-Claviers-Citoyens/API/rest_tournament.php?id=1" -Headers @{ 'Authorization' = "Bearer $token" }
if ($method === 'DELETE') {
    $data = getJsonInput();
    if (!$idT) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing tournament ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM tournament WHERE id = :id');
        $stmt->execute(['id' => $idT]);
        http_response_code(200);
        echo json_encode(['message' => 'tournament deleted']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed. Use POST, PUT or DELETE']);




