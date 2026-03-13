<?php
// rest_match.php - Gestion des matchs
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/getJsonInput.php';

if (!isset($secure) || $secure !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Access forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
// Variables definies par index.php:
// $idT = ID tournoi
// $idM = ID match
// $isPointRoute = true si route /point

// Route PUT /tournaments/{id}/matches/{matchId}/point
if (isset($isPointRoute) && $isPointRoute === true && $method === 'PUT') {
    if (!$idT || !$idM) {
        http_response_code(400);
        echo json_encode(['error' => 'Tournament ID and Match ID are required']);
        exit;
    }

    $data = getJsonInput();
    $teamId = isset($data['teamId']) ? (int)$data['teamId'] : null;
    $teamPoint = isset($data['teamPoint']) ? (int)$data['teamPoint'] : null;

    if ($teamId === null || $teamPoint === null) {
        http_response_code(400);
        echo json_encode(['error' => 'teamId and teamPoint are required']);
        exit;
    }

    try {
        // Verifier que le match existe et appartient au tournoi
        $stmt = $pdo->prepare('
            SELECT m.id, m.team1_id, m.team2_id
            FROM `MATCH` m
            JOIN `ROUND` r ON m.idR = r.id
            WHERE m.id = :idM AND r.idT = :idT
        ');
        $stmt->execute([':idM' => $idM, ':idT' => $idT]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$match) {
            http_response_code(404);
            echo json_encode(['error' => 'Match not found']);
            exit;
        }

        // Determiner quelle equipe mettre a jour
        if ($teamId == $match['team1_id']) {
            $stmt = $pdo->prepare('UPDATE `MATCH` SET team1_point = :point WHERE id = :id');
        } elseif ($teamId == $match['team2_id']) {
            $stmt = $pdo->prepare('UPDATE `MATCH` SET team2_point = :point WHERE id = :id');
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Team is not part of this match']);
            exit;
        }

        $stmt->execute([':point' => $teamPoint, ':id' => $idM]);

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Route GET /tournaments/{id}/matches ou /tournaments/{id}/matches/{matchId}
if ($method === 'GET') {
    try {
        // Si on a un ID de tournoi, filtrer par tournoi
        if (isset($idT) && $idT) {
            if (isset($idM) && $idM) {
                // Un match specifique
                $stmt = $pdo->prepare('
                    SELECT m.id, m.team1_id, m.team2_id, m.team1_point, m.team2_point, m.idR
                    FROM `MATCH` m
                    JOIN `ROUND` r ON m.idR = r.id
                    WHERE m.id = :idM AND r.idT = :idT
                ');
                $stmt->execute([':idM' => $idM, ':idT' => $idT]);
                $match = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$match) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Match not found']);
                    exit;
                }

                // Convertir en entiers
                $match['id'] = (int)$match['id'];
                $match['team1_id'] = $match['team1_id'] ? (int)$match['team1_id'] : null;
                $match['team2_id'] = $match['team2_id'] ? (int)$match['team2_id'] : null;
                $match['team1_point'] = (int)$match['team1_point'];
                $match['team2_point'] = (int)$match['team2_point'];
                $match['idR'] = (int)$match['idR'];

                echo json_encode(['data' => $match]);
            } else {
                // Tous les matchs d'un tournoi
                $stmt = $pdo->prepare('
                    SELECT m.id, m.team1_id, m.team2_id, m.team1_point, m.team2_point, m.idR
                    FROM `MATCH` m
                    JOIN `ROUND` r ON m.idR = r.id
                    WHERE r.idT = :idT
                    ORDER BY r.id, m.id
                ');
                $stmt->execute([':idT' => $idT]);
                $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Convertir en entiers
                foreach ($matches as &$match) {
                    $match['id'] = (int)$match['id'];
                    $match['team1_id'] = $match['team1_id'] ? (int)$match['team1_id'] : null;
                    $match['team2_id'] = $match['team2_id'] ? (int)$match['team2_id'] : null;
                    $match['team1_point'] = (int)$match['team1_point'];
                    $match['team2_point'] = (int)$match['team2_point'];
                    $match['idR'] = (int)$match['idR'];
                }

                echo json_encode(['data' => $matches]);
            }
        }
        // Route /match ou /match/{id}
        elseif (isset($idM) && $idM) {
            $stmt = $pdo->prepare('SELECT id, team1_id, team2_id, team1_point, team2_point, idR FROM `MATCH` WHERE id = :id');
            $stmt->execute([':id' => $idM]);
            $match = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$match) {
                http_response_code(404);
                echo json_encode(['error' => 'Match not found']);
                exit;
            }

            $match['id'] = (int)$match['id'];
            $match['team1_id'] = $match['team1_id'] ? (int)$match['team1_id'] : null;
            $match['team2_id'] = $match['team2_id'] ? (int)$match['team2_id'] : null;
            $match['team1_point'] = (int)$match['team1_point'];
            $match['team2_point'] = (int)$match['team2_point'];
            $match['idR'] = (int)$match['idR'];

            echo json_encode(['data' => $match]);
        } else {
            // Tous les matchs
            $stmt = $pdo->query('SELECT id, team1_id, team2_id, team1_point, team2_point, idR FROM `MATCH` ORDER BY id');
            $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($matches as &$match) {
                $match['id'] = (int)$match['id'];
                $match['team1_id'] = $match['team1_id'] ? (int)$match['team1_id'] : null;
                $match['team2_id'] = $match['team2_id'] ? (int)$match['team2_id'] : null;
                $match['team1_point'] = (int)$match['team1_point'];
                $match['team2_point'] = (int)$match['team2_point'];
                $match['idR'] = (int)$match['idR'];
            }

            echo json_encode(['data' => $matches]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Route POST - Creer un match
if ($method === 'POST') {
    $data = getJsonInput();
    $team1_id = isset($data['team1_id']) ? (int)$data['team1_id'] : null;
    $team2_id = isset($data['team2_id']) ? (int)$data['team2_id'] : null;
    $idR = isset($data['idR']) ? (int)$data['idR'] : null;

    if (!$idR) {
        http_response_code(400);
        echo json_encode(['error' => 'idR (round ID) is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO `MATCH` (team1_id, team2_id, team1_point, team2_point, idR) VALUES (:team1, :team2, 0, 0, :idR)');
        $stmt->execute([':team1' => $team1_id, ':team2' => $team2_id, ':idR' => $idR]);
        $newId = $pdo->lastInsertId();

        http_response_code(201);
        echo json_encode([
            'data' => [
                'id' => (int)$newId,
                'team1_id' => $team1_id,
                'team2_id' => $team2_id,
                'team1_point' => 0,
                'team2_point' => 0,
                'idR' => $idR
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Route DELETE - Supprimer un match
if ($method === 'DELETE') {
    if (!isset($idM) || !$idM) {
        http_response_code(400);
        echo json_encode(['error' => 'Match ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM `MATCH` WHERE id = :id');
        $stmt->execute([':id' => $idM]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Match not found']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Match deleted']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
