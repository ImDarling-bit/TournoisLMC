<?php

$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

$use_remote_api = false;
$remote_api_url = 'https://votre-serveur-api.com/API';

$doc_root     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$project_root = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
$base_path    = $doc_root !== '' ? str_ireplace($doc_root, '', $project_root) : '';

$api_url = $use_remote_api
    ? rtrim($remote_api_url, '/')
    : "$protocol://$host$base_path/API";

return [

    'db' => [
        'host'    => 'localhost',
        'name'    => 'db',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    'api_url' => $api_url,
];
