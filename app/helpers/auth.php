<?php
/**
 * Location: vetapp/app/helpers/auth.php
 */

declare(strict_types=1);

if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ************ VERIFICAR SI EL USUARIO ESTA LOGGEADO ************ 
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

//  ************  RETURNA LOS ROLES  ************ 
function userRoles():array {
    return $_SESSION['user']['roles'] ?? [];
}

//  ************ VERIFICAR SI EL USUARIO TIENE ROL ************ 
function hasRole(string $role): bool {
    return in_array($role, userRoles(), true);
}

//  ************ VERIFICAR SI EL USUARIO TIENE ROL  ************ 
function hasAnyRole(array $roles): bool {
    foreach ($roles as $role) {
        if(hasrole($role)) {
            return true;
        }
    }
    return false;
}