<?php
// config.php - mettez à jour ces valeurs pour votre environnement
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'db',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8',
    ],

    // Clé secrète utilisée pour signer les JWT (chaîne aléatoire forte).
    'jwt_secret' => 'Hj26trD*p5FDSni_3kL@9vXzQ8wRmYbC',

    // Durée de vie du token en secondes
    'access_token_lifetime' => 3600,
];
