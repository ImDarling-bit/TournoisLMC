<?php
session_start();

$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$token = $_SESSION['api_token'] ?? '';
$result = null;

// Requete API
function api($url, $method = 'GET', $data = null, $token = null, $isForm = false) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $token
            ? ['Authorization: Bearer ' . $token, 'Content-Type: application/json']
            : ($isForm ? ['Content-Type: application/x-www-form-urlencoded'] : ['Content-Type: application/json'])
    ]);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $isForm ? $data : json_encode($data));
    }
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($response, true), 'raw' => $response];
}

// Actions
$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $data = "grant_type=password&email=" . urlencode($_POST['email']) . "&password=" . urlencode($_POST['pass']);
    $result = api($baseUrl . '/auth/token', 'POST', $data, null, true);
    if ($result['code'] == 200 && isset($result['data']['access_token'])) {
        $_SESSION['api_token'] = $result['data']['access_token'];
        $token = $result['data']['access_token'];
    }
}
elseif ($action === 'register') {
    $result = api($baseUrl . '/auth/register', 'POST', [
        'email' => $_POST['email'],
        'password' => $_POST['pass'],
        'name' => $_POST['name']
    ]);
}
elseif ($action === 'logout') {
    unset($_SESSION['api_token']);
    $token = '';
}
elseif ($action === 'test' && $token) {
    $endpoint = $_POST['endpoint'] ?? '';
    $method = $_POST['method'] ?? 'GET';
    $body = $_POST['body'] ?? '';
    $jsonBody = $body ? json_decode($body, true) : null;
    $result = api($baseUrl . $endpoint, $method, $jsonBody, $token);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test API</title>
</head>
<body>
<h1>Test API</h1>
<p><b>URL:</b> <?= htmlspecialchars($baseUrl) ?></p>
<p><b>Token:</b> <?= $token ? '&#10004; Connecte' : '&#10008; Non connecte' ?></p>
<hr>

<?php if (!$token): ?>
<h2>Connexion</h2>
<form method="post">
    <input type="hidden" name="action" value="login">
    <input type="text" name="email" placeholder="Email" value="toto@net.fr" required>
    <input type="password" name="pass" placeholder="Mot de passe" value="toto" required>
    <button type="submit">Connexion</button>
</form>

<h2>Inscription</h2>
<form method="post">
    <input type="hidden" name="action" value="register">
    <input type="text" name="name" placeholder="Nom" required>
    <input type="text" name="email" placeholder="Email" required>
    <input type="password" name="pass" placeholder="Mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>

<?php else: ?>
<form method="post" style="display:inline">
    <input type="hidden" name="action" value="logout">
    <button type="submit">Deconnexion</button>
</form>
<hr>

<h2>Tester un endpoint</h2>
<form method="post">
    <input type="hidden" name="action" value="test">
    <select name="method">
        <option>GET</option>
        <option>POST</option>
        <option>PUT</option>
        <option>DELETE</option>
    </select>
    <select name="endpoint" id="endpoint" onchange="updateBody()">
        <option value="/auth/me">GET /auth/me</option>
        <option value="/users">GET /users</option>
        <option value="/users/1">GET /users/1</option>
        <option value="/tournaments">GET /tournaments</option>
        <option value="/tournaments/1">GET /tournaments/1</option>
        <option value="/tournaments">POST /tournaments (creer)</option>
        <option value="/tournaments/1/teams">GET /tournaments/1/teams</option>
        <option value="/tournaments/1/teams">POST /tournaments/1/teams (creer)</option>
        <option value="/tournaments/1/matches">GET /tournaments/1/matches</option>
        <option value="/tournaments/1/rounds">GET /tournaments/1/rounds</option>
    </select>
    <br><br>
    <textarea name="body" id="body" rows="3" cols="60" placeholder='{"name":"Test","game":"Jeu","teamcount":4}'></textarea>
    <br>
    <button type="submit">Envoyer</button>
</form>

<script>
function updateBody() {
    var sel = document.getElementById('endpoint');
    var body = document.getElementById('body');
    var opt = sel.options[sel.selectedIndex].text;
    if (opt.includes('POST /tournaments/1/teams')) {
        body.value = '{"name":"Equipe Test"}';
    } else if (opt.includes('POST /tournaments')) {
        body.value = '{"name":"Tournoi Test","game":"Jeu Test","teamcount":4}';
    } else {
        body.value = '';
    }
}
</script>
<?php endif; ?>

<?php if ($result): ?>
<hr>
<h2>Resultat</h2>
<p><b>Code HTTP:</b>
    <span style="color:<?= ($result['code'] >= 200 && $result['code'] < 300) ? 'green' : 'red' ?>">
        <?= $result['code'] ?>
    </span>
</p>
<pre><?= htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: $result['raw']) ?></pre>
<?php endif; ?>

</body>
</html>
