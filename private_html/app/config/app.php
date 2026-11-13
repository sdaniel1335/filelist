<?php
// define('APP_NAME', 'VC Framework');
// define('APP_PROJECT_URL', 'https://github.com/dsoos1290/vcframework');
define('APP_VER', '0.1.0');
define('APP_TZ', 'Europe/Budapest');
define('APP_ENV', 'prod');
define('APP_LANG', 'hu');
define('APP_TITLE', 'Filelist');
define('APP_SESSION', true); // flash
define('FILE_MAX_BYTES', 25 * 1024 * 1024);
// Default to a sibling of the actual document root, including subdirectory installs.
// This can be replaced with an explicit absolute path on deployment.
$file_document_root = isset($_SERVER['DOCUMENT_ROOT'])
  ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
define('FILE_STORAGE_PATH', ($file_document_root !== false
  ? dirname($file_document_root)
  : dirname(dirname(dirname(dirname(__DIR__))))) . '/filelist-storage');
unset($file_document_root);
// Loaded before the framework starts the session; supported on PHP 5.3 too.
if (PHP_SAPI !== 'cli' && session_id() === '') {
  ini_set('session.use_only_cookies', '1');
  ini_set('session.use_trans_sid', '0');
  ini_set('session.cookie_httponly', '1');
  ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? '1' : '0');
  if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
    ini_set('session.cookie_samesite', 'Lax');
  }
}
// define('APP_URL', 'https://example.com');
