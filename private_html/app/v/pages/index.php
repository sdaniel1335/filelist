<div class="container filelist">
  <h1>Fájlok</h1>
  <form class="upload" method="post" enctype="multipart/form-data" action="<?php echo url('/upload'); ?>">
    <input type="hidden" name="_csrf" value="<?php echo file_escape(auth_csrf_token()); ?>">
    <label>Fájl <input type="file" name="file" required></label>
    <label>Mappa <select name="folder"><option>B</option><option>P</option><option>W</option></select></label>
    <button type="submit">Feltöltés</button>
    <small>Legfeljebb 25 MB / fájl.</small>
  </form>
  <?php $has_files = false; foreach ($groups as $folder => $items) { if (!$items) { continue; } $has_files = true; ?>
  <section class="folder">
    <h2><?php echo file_escape($folder); ?> <small>(<?php echo count($items); ?>)</small></h2>
    <div class="table-scroll"><table>
      <thead><tr><th>Fájlnév</th><th>Méret</th><th>Feltöltés</th><th>Műveletek</th></tr></thead>
      <tbody><?php foreach ($items as $file) { ?>
        <tr>
          <td class="filename"><a href="<?php echo url('/download'); ?>?id=<?php echo (int) $file['id']; ?>"><?php echo file_escape($file['name']); ?></a></td>
          <td><?php echo number_format($file['size_bytes'] / 1024, 1, ',', ' '); ?> KB</td>
          <td><?php echo file_escape($file['created_at']); ?></td>
          <td><div class="file-actions">
            <a href="<?php echo url('/download'); ?>?id=<?php echo (int) $file['id']; ?>">Letöltés</a>
            <details><summary>Átnevezés</summary><form method="post" action="<?php echo url('/rename'); ?>">
              <input type="hidden" name="_csrf" value="<?php echo file_escape(auth_csrf_token()); ?>">
              <input type="hidden" name="id" value="<?php echo (int) $file['id']; ?>">
              <input type="text" name="name" aria-label="Új fájlnév" value="<?php echo file_escape($file['name']); ?>" required>
              <button type="submit">Mentés</button>
            </form></details>
            <form method="post" action="<?php echo url('/delete'); ?>" class="delete-file">
              <input type="hidden" name="_csrf" value="<?php echo file_escape(auth_csrf_token()); ?>">
              <input type="hidden" name="id" value="<?php echo (int) $file['id']; ?>">
              <button type="submit" class="danger">Törlés</button>
            </form>
          </div></td>
        </tr>
      <?php } ?></tbody>
    </table></div>
  </section>
  <?php } if (!$has_files) { ?><p class="empty">Még nincs feltöltött fájlod. Válassz egy fájlt és egy mappát a feltöltéshez.</p><?php } ?>
</div>