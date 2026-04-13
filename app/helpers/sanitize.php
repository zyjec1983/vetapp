<?php
/**
 * Location: vetapp/app/helpers/sanitize.php
 */

/**
 * Sanitiza un string (trim, stripslashes, htmlspecialchars)
 */
function sanitizeInput($data){
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Sanitiza un array completo recursivamente
 */
function sanitizeArray($array){
    if (!is_array($array)){
        return sanitizeInput($array);
    }
    return array_map('sanitizeArray', $array);
}