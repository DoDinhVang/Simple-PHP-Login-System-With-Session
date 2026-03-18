<?php
require_once 'vendor/autoload.php'; //Load toàn bộ thư viện Composer vào project

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$config = require_once 'config.php';
$secret_key = $config['jwt_secret'];
$payload = [
    'name' => "dd.vang.tra",
    'email' => 'ddvang@gmail.com'
];
$jwt = JWT::encode($payload, $secret_key, "HS256");
$decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

print_r($decoded);
