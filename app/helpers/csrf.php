<?php
/**
 * Location: vetapp/app/helpers/csrf.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Genera token de (CSRF -> Cross-Site Request Forgery) y lo guarda en la session
 */
function generateCSRFToken() {
    if(empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));        
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica que el token enviado coincida con la sesion
 */
function verifyCSRFToken($token){
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
