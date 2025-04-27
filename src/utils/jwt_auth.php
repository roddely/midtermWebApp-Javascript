<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

const JWT_SECRET = 'your_super_secret_key_123456';
const JWT_EXPIRE = 3600; // 1 giờ

function create_jwt($user_id, $user_name) {
    $payload = [
        'user_id' => $user_id,
        'user_name' => $user_name,
        'iat' => time(),
        'exp' => time() + JWT_EXPIRE,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'ua' => $_SERVER['HTTP_USER_AGENT']
    ];
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

function verify_jwt() {
    if (!isset($_COOKIE['auth_token'])) {
        return false;
    }
    try {
        $decoded = JWT::decode($_COOKIE['auth_token'], new Key(JWT_SECRET, 'HS256'));
        if ($decoded->ip !== $_SERVER['REMOTE_ADDR'] || $decoded->ua !== $_SERVER['HTTP_USER_AGENT']) {
            return false;
        }
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

function require_jwt_auth() {
    $jwt = verify_jwt();
    if (!$jwt) {
        setcookie('auth_token', '', time() - 3600, '/');
        header('Location: login.php');
        exit();
    }
    return $jwt;
} 