<?php
/**
 * COPS (Calibre OPDS PHP Server) config loader
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config_default.php';
if (file_exists(__DIR__ . '/config_local.php') && (php_sapi_name() !== 'cli')) {
    try {
        require __DIR__ . '/config_local.php';
    } catch (Throwable $e) {
        echo "Error loading config_local.php<br>\n";
        echo $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine();
        exit;
    }
}
/** @var array<mixed> $config */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Calibre\User;

// Get user authentication from server
$http_auth_user = $config['cops_http_auth_user'] ?? 'PHP_AUTH_USER';
$remote_user = array_key_exists($http_auth_user, $_SERVER) ? $_SERVER[$http_auth_user] : '';
// Clean username, only allow a-z, A-Z, 0-9, -_ chars
$remote_user = preg_replace('/[^a-zA-Z0-9_-]/', '', $remote_user);
if (!empty($remote_user)) {
    $user_config_file = 'config_local.' . $remote_user . '.php';
    if (file_exists(__DIR__ . '/' . $user_config_file) && (php_sapi_name() !== 'cli')) {
        require __DIR__ . '/' . $user_config_file;
    }
}

// from here on, we assume that all global $config variables have been loaded
Config::load($config);
date_default_timezone_set(Config::get('default_timezone'));

if (!User::verifyLogin($_SERVER)) {
    header('WWW-Authenticate: Basic realm="COPS Authentication"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'This site is password protected';
    exit;
}
