<?php
// jwt.php - Fonctions JWT (HS256)

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $data .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_encode(array $payload, string $secret): string {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $segments = [];
    $segments[] = base64url_encode(json_encode($header));
    $segments[] = base64url_encode(json_encode($payload));
    $signing_input = implode('.', $segments);
    $signature = hash_hmac('sha256', $signing_input, $secret, true);
    $segments[] = base64url_encode($signature);
    return implode('.', $segments);
}

function jwt_decode(string $jwt, string $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return null;
    list($b64head, $b64payload, $b64sig) = $parts;
    $head = json_decode(base64url_decode($b64head), true);
    $payload = json_decode(base64url_decode($b64payload), true);
    $sig = base64url_decode($b64sig);

    if (empty($head) || empty($payload) || $sig === false) return null;
    // vérifier l'algorithme
    if (!isset($head['alg']) || $head['alg'] !== 'HS256') return null;

    $raw_sig = hash_hmac('sha256', "$b64head.$b64payload", $secret, true);
    if (!hash_equals($raw_sig, $sig)) return null;

    // vérifier l'expiration si présente
    if (isset($payload['exp']) && time() >= $payload['exp']) return null;

    return $payload;
}
