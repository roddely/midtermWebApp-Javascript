<?php
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generateVerificationToken() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isStrongPassword($password) {
    // Ít nhất 8 ký tự
    if (strlen($password) < 8) {
        return false;
    }
    
    // Phải chứa ít nhất một chữ cái và một số
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return false;
    }
    
    return true;
} 