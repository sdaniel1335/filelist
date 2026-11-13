<?php
// Standalone application checks; no framework or database changes required.
class c {}
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require dirname(__DIR__) . '/private_html/app/config/app.php';
require dirname(__DIR__) . '/private_html/app/c/_app.php';
require dirname(__DIR__) . '/private_html/app/c/files.php';
$_SESSION = array();

function check($condition, $message)
{
  if (!$condition) { throw new RuntimeException($message); }
}
function url($path) { return $path; }

check(FILE_STORAGE_PATH === dirname(realpath($_SERVER['DOCUMENT_ROOT'])) . '/filelist-storage', 'Storage default must be outside the document root.');

foreach (array('report.pdf', 'árvíztűrő tükör.txt', 'file.php', 'a"b.txt') as $name) {
  check(file_name_valid($name), 'Valid filename rejected.');
}
foreach (array('', ' ', '.', '..', '../secret', 'a/b', 'a\\b', "a\r\nb", "a\0b", str_repeat('a', 241), array(), "\xff") as $name) {
  check(!file_name_valid($name), 'Unsafe filename accepted.');
}
check(file_escape('<script>"') === '&lt;script&gt;&quot;', 'Escaping failed.');
$token = auth_csrf_token();
check(strlen($token) === 64 && auth_csrf_valid($token), 'CSRF generation failed.');
check(!auth_csrf_valid('wrong') && !auth_csrf_valid(array()), 'Invalid CSRF accepted.');
check(!auth_logged_in(), 'Anonymous session accepted.');
$_SESSION['auth_user_id'] = 42;
check(auth_user_id() === 42, 'User identity failed.');
$hash = auth_password_make('correct password');
check(auth_password_check('correct password', $hash), 'Password verification failed.');
check(!auth_password_check('wrong password', $hash), 'Wrong password accepted.');
$salt = 'test-salt';
$legacy_hash = 'pbkdf2_sha256$1000$' . $salt . '$' . bin2hex(auth_pbkdf2('legacy password', $salt, 1000, 32));
check(auth_password_check('legacy password', $legacy_hash), 'Legacy password failed.');

function render_groups($groups)
{
  ob_start();
  require dirname(__DIR__) . '/private_html/app/v/pages/index.php';
  return ob_get_clean();
}
$sample = array('id' => 1, 'name' => '<script>.txt', 'size_bytes' => 10, 'created_at' => '2026-11-13 10:00:00');
$html = render_groups(array('B' => array($sample), 'P' => array(), 'W' => array($sample)));
check(substr_count($html, '<section class="folder">') === 2, 'Empty folder displayed.');
check(strpos($html, '<h2>P') === false, 'Empty P folder displayed.');
check(strpos($html, '&lt;script&gt;.txt') !== false && strpos($html, '<script>.txt') === false, 'Filename HTML injection.');
$html = render_groups(array('B' => array(), 'P' => array(), 'W' => array()));
check(strpos($html, '<section class="folder">') === false && strpos($html, 'class="empty"') !== false, 'Empty state failed.');
echo "Security and view checks passed.\n";
