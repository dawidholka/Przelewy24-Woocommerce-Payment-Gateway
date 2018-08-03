<?php

if (!defined('PRZELEWY24_LIB_ROOT')) {
    define('PRZELEWY24_LIB_ROOT', dirname(__FILE__));
}

/**
 * TODO: use autoload? It is possible in presta, magento, PHP5.2 ?
 */
require_once PRZELEWY24_LIB_ROOT . DIRECTORY_SEPARATOR . 'interfaces' . DIRECTORY_SEPARATOR . 'Przelewy24Interface.php';
require_once PRZELEWY24_LIB_ROOT . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Przelewy24Product.php';
require_once PRZELEWY24_LIB_ROOT . DIRECTORY_SEPARATOR . 'installer' . DIRECTORY_SEPARATOR . 'Przelewy24Installer.php';
require_once PRZELEWY24_LIB_ROOT . DIRECTORY_SEPARATOR . 'zencard' . DIRECTORY_SEPARATOR . 'ZenCardApi.php';
