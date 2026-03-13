<?php
// index.php - routeur simple pour l'API
require __DIR__ . '/db.php'; // ensures $pdo exists and users table created
$config = require __DIR__ . '/config.php';
require __DIR__ . '/jwt.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
if ($base === '/') $base = '';
$path = substr($uri, strlen($base));
$path = '/' . trim($path, '/');

// Route publique : /auth/token
if ($path === '/auth/token') {
    require __DIR__ . '/token.php';
    exit;
}

// Route publique : /auth/login
if ($path === '/auth/login') {
    require __DIR__ . '/token.php';
    exit;
}

// Route publique : /auth/register
if ($path === '/auth/register') {
    require __DIR__ . '/register.php';
    exit;
}

$secure = true; // Pour empêcher l'utilisation directe de l'API sans passer par index.php

// pour le débogage
//file_put_contents(__DIR__.'/hdrs.log',$path ."\n". print_r(getallheaders(), true)."\n".print_r($_SERVER, true)."\n", FILE_APPEND);

// pour les autres routes, l'en-tête Authorization est requis
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);
if (!$auth) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'invalid_request', 'error_description' => 'Authorization header required']);
    exit;
}

if (!preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'invalid_request', 'error_description' => 'Bearer token required']);
    exit;
}
$token = $m[1];
$payload = jwt_decode($token, $config['jwt_secret']);
if (!$payload) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'invalid_token', 'error_description' => 'token invalid or expired']);
    exit;
}

// attacher $user au global pour les endpoints
//$user = $payload;

// Routage

// Route publique : /auth/me
if ($path === '/auth/me') {
    require __DIR__ . '/me.php';
    exit;
}

// Routes : /users ou /users/{id}
if (preg_match('#^/users(?:/([0-9]+))?$#', $path, $m)) {
    $id = $m[1] ?? null;
    require __DIR__ . '/rest_user.php';
    exit;
}

// Routes : /users/{userId}/password
if (preg_match('#^/users/([0-9]+)/password$#', $path, $m)) {
    $id = $m[1] ?? null;
    require __DIR__ . '/rest_user.php';
    exit;
}

// Routes : /tournaments ou /tournaments/{idT}
if (preg_match('#^/tournaments(?:/([0-9]+))?$#', $path, $m)) {
    $idT = $m[1] ?? null;
    require __DIR__ . '/rest_tournament.php';
    exit;
}

// Routes : /tournaments/{tournamentsId}/teams
if (preg_match('#^/tournaments/([0-9]+)/teams$#', $path, $m)) {
    $idT = $m[1] ?? null;
    require __DIR__ . '/rest_team.php';
    exit;
}

// Routes : api/v1/tournaments/{tournamentsId}/teams/{teamId}
if (preg_match('#^/tournaments/([0-9]+)/teams/([0-9]+)$#', $path, $m)) {
    $idT = $m[1] ?? null;
    $idTeam = $m[2] ?? null;
    require __DIR__ . '/rest_team.php';
    exit;
}

// Routes : /tournaments/{tournamentsId}/rounds
if (preg_match('#^/tournaments/([0-9]+)/rounds$#', $path, $m)) {
    $idT = $m[1] ?? null;
    require __DIR__ . '/rest_round.php';
    exit;
}

// Routes : /tournaments/{tournamentsId}/rounds/{roundId}
if (preg_match('#^/tournaments/([0-9]+)/rounds/([0-9]+)$#', $path, $m)) {
    $idT = $m[1] ?? null;
    $idR = $m[2] ?? null;
    require __DIR__ . '/rest_round.php';
    exit;
}

// Routes : /tournaments/{tournamentsId}/matches
if (preg_match('#^/tournaments/([0-9]+)/matches$#', $path, $m)) {
    $idT = $m[1] ?? null;
    require __DIR__ . '/rest_match.php';
    exit;
}

// Routes : /tournaments/{tournamentsId}/matches/{matchId}
if (preg_match('#^/tournaments/([0-9]+)/matches/([0-9]+)$#', $path, $m)) {
    $idT = $m[1] ?? null;
    $idM = $m[2] ?? null;
    require __DIR__ . '/rest_match.php';
    exit;
}

// Routes : /tournaments/{tournamentsId}/matches/{matchId}/point
if (preg_match('#^/tournaments/([0-9]+)/matches/([0-9]+)/point$#', $path, $m)) {
    $idT = $m[1] ?? null;
    $idM = $m[2] ?? null;
    $isPointRoute = true;
    require __DIR__ . '/rest_match.php';
    exit;
}

// Routes : /rounds ou /rounds/{idR}
if (preg_match('#^/rounds(?:/([0-9]+))?$#', $path, $m)) {
    $idR = $m[1] ?? null;
    require __DIR__ . '/rest_round.php';
    exit;
}

// Routes : /match ou /match/{idM}
if (preg_match('#^/match(?:/([0-9]+))?$#', $path, $m)) {
    $idM = $m[1] ?? null;
    require __DIR__ . '/rest_match.php';
    exit;
}

header('Content-Type: application/json');
http_response_code(404);
echo json_encode(['error' => 'not_found']);
