<?php
/**
 * Location: vetapp/app/config/config.php
 */

declare(strict_types=1);

// ***************** CONFIGURACIONES GENERALES *****************
// ZONA HORARIA
date_default_timezone_set('America/Guayaquil');

// RUTAS DEL SISTEMA (FÍSICAS)
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', APP_PATH . '/config');
define('CONTROLLER_PATH', APP_PATH . '/controllers');
define('MODEL_PATH', APP_PATH . '/models');
define('VIEW_PATH', APP_PATH . '/views');
define('MIDDLEWARE_PATH', APP_PATH . '/middleware');
define('HELPER_PATH', APP_PATH . '/helpers');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// ***************** BASE URL DEL SISTEMA *****************
define('BASE_URL', 'http://localhost/vetapp/public/');

// ***************** ENTORNO *****************
define('ENV', 'development'); // production
