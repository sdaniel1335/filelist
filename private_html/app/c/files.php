<?php
function file_name_valid($name)
{
  return is_string($name) && trim($name) !== '' && strlen($name) <= 240
    && $name !== '.' && $name !== '..'
    && !preg_match('/[\x00-\x1f\x7f\/\\\\]/', $name) && preg_match('//u', $name);
}
function file_escape($value)
{
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
class files extends app
{
  private function storage_path()
  {
    $path = FILE_STORAGE_PATH;
    if (!is_dir($path) && !@mkdir($path, 0700, true)) {
      $this->fail('A privát fájltároló nem hozható létre. Ellenőrizd a FILE_STORAGE_PATH beállítását és a könyvtár írási jogosultságát.');
    }
    $path = realpath($path);
    $document_root = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    if (!$path || !$document_root) {
      $this->fail('A privát fájltároló helye nem ellenőrizhető. Ellenőrizd a FILE_STORAGE_PATH és a webszerver document root beállítását.');
    }
    $normalized_path = strtolower(str_replace('\\', '/', $path)) . '/';
    $normalized_root = strtolower(str_replace('\\', '/', $document_root)) . '/';
    $protected_storage = realpath(dirname(dirname(__DIR__)) . '/storage');
    $normalized_storage = $protected_storage !== false
      ? strtolower(str_replace('\\', '/', $protected_storage)) . '/' : '';
    $private_storage = $normalized_storage !== ''
      && strpos($normalized_path, $normalized_storage) === 0
      && is_file($protected_storage . '/.htaccess');
    if (strpos($normalized_path, $normalized_root) === 0 && !$private_storage) {
      $this->fail('A FILE_STORAGE_PATH csak a védett private_html/storage könyvtárra vagy a document rooton kívüli tárolóra mutathat.');
    }
    return $path;
  }
  private function fail($message)
  {
    $this->flash('error', $message);
    $this->redirect('/');
  }
  private function owned_file()
  {
    $input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $id = isset($input['id']) && is_string($input['id']) && ctype_digit($input['id']) ? (int) $input['id'] : 0;
    $user_id = auth_user_id();
    $stmt = $this->db->prepare('SELECT id, storage_key, name, size_bytes FROM ' . $this->table('files') . ' WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $stmt->bind_result($file_id, $storage_key, $name, $size_bytes);
    $found = $stmt->fetch();
    $stmt->close();
    if (!$found || !preg_match('/^[a-f0-9]{64}$/D', $storage_key)) {
      header('HTTP/1.1 404 Not Found');
      exit('A fájl nem található.');
    }
    return array('id' => $file_id, 'storage_key' => $storage_key, 'name' => $name, 'size_bytes' => $size_bytes);
  }
  public function index()
  {
    $user_id = auth_user_id();
    $stmt = $this->db->prepare('SELECT id, folder, name, size_bytes, created_at FROM ' . $this->table('files') . ' WHERE user_id = ? ORDER BY name, id');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($id, $folder, $name, $size_bytes, $created_at);
    $groups = array('B' => array(), 'P' => array(), 'W' => array());
    while ($stmt->fetch()) {
      if (isset($groups[$folder])) {
        $groups[$folder][] = array('id' => $id, 'name' => $name, 'size_bytes' => $size_bytes, 'created_at' => $created_at);
      }
    }
    $stmt->close();
    $this->set('groups', $groups);
    $this->set('title', 'Filelist');
    $this->render('pages/index');
  }
  public function upload()
  {
    $this->require_csrf();
    $folder = isset($_POST['folder']) && is_string($_POST['folder']) ? $_POST['folder'] : '';
    if (!in_array($folder, array('B', 'P', 'W'), true)) {
      $this->fail('Válassz B, P vagy W mappát.');
    }
    $file = isset($_FILES['file']) ? $_FILES['file'] : null;
    if (!is_array($file) || !isset($file['error'], $file['name'], $file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK
      || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
      $this->fail('Sikertelen feltöltés. Ellenőrizd a fájlt és a szerver méretkorlátját.');
    }
    $name = $file['name'];
    if (!file_name_valid($name)) { $this->fail('Érvénytelen vagy túl hosszú fájlnév (legfeljebb 240 bájt).'); }
    $size_bytes = filesize($file['tmp_name']);
    if ($size_bytes === false || $size_bytes > FILE_MAX_BYTES) { $this->fail('A fájl legfeljebb 25 MB lehet.'); }
    $storage_key = bin2hex(auth_random_bytes(32));
    $path = $this->storage_path() . DIRECTORY_SEPARATOR . $storage_key;
    $handle = @fopen($path, 'x+b');
    if (!$handle) { $this->fail('A fájl tárolása sikertelen.'); }
    @chmod($path, 0600);
    $source = fopen($file['tmp_name'], 'rb');
    $written = $source ? stream_copy_to_stream($source, $handle) : false;
    if ($source) { fclose($source); }
    fclose($handle);
    if ($written === false || $written != $size_bytes) {
      @unlink($path);
      $this->fail('A fájl tárolása sikertelen.');
    }
    try {
      $user_id = auth_user_id();
      $stmt = $this->db->prepare('INSERT INTO ' . $this->table('files') . ' (user_id, folder, storage_key, name, size_bytes, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
      $stmt->bind_param('isssi', $user_id, $folder, $storage_key, $name, $size_bytes);
      $stmt->execute();
      $stmt->close();
    } catch (Exception $error) {
      @unlink($path);
      throw $error;
    }
    $this->flash('success', 'Fájl feltöltve.');
    $this->redirect('/');
  }
  public function rename()
  {
    $this->require_csrf();
    $file = $this->owned_file();
    $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    if (!file_name_valid($name)) { $this->fail('Érvénytelen vagy túl hosszú fájlnév.'); }
    $user_id = auth_user_id();
    $stmt = $this->db->prepare('UPDATE ' . $this->table('files') . ' SET name = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sii', $name, $file['id'], $user_id);
    $stmt->execute();
    $stmt->close();
    $this->flash('success', 'Fájl átnevezve.');
    $this->redirect('/');
  }
  public function delete()
  {
    $this->require_csrf();
    $file = $this->owned_file();
    $path = $this->storage_path() . DIRECTORY_SEPARATOR . $file['storage_key'];
    if ((file_exists($path) || is_link($path)) && !unlink($path)) { $this->fail('A fájl törlése sikertelen.'); }
    $user_id = auth_user_id();
    $stmt = $this->db->prepare('DELETE FROM ' . $this->table('files') . ' WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $file['id'], $user_id);
    $stmt->execute();
    $stmt->close();
    $this->flash('success', 'Fájl törölve.');
    $this->redirect('/');
  }
  public function download()
  {
    $file = $this->owned_file();
    $path = $this->storage_path() . DIRECTORY_SEPARATOR . $file['storage_key'];
    $handle = !is_link($path) ? @fopen($path, 'rb') : false;
    if (!$handle) { header('HTTP/1.1 404 Not Found'); exit('A fájl nem található.'); }
    $stat = fstat($handle);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="download"; filename*=UTF-8\'\'' . rawurlencode($file['name']));
    header('Content-Length: ' . $stat['size']);
    header('Content-Security-Policy: sandbox');
    session_write_close();
    fpassthru($handle);
    fclose($handle);
    exit;
  }
}
