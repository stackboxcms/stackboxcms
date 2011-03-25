<?php
use Module\Filebrowser\File;
?>

<div class="cms_filebrowser_list_dirs">
    <?php
    // Directories
    $dirs = $kernel->finder()
      ->directories()
      ->in($directory)
      ->depth(1)
      ->sortByName();
    
    // Has
    if($dirCount = count($dirs) > 1):
        foreach($dirs as $mDir):
        $dir = new File($mDir);
      ?>
        <div class="cms_filebrowser_tile cms_filebrowser_dir">
          <a href="<?php echo $dir->getPathname(); ?>"><?php echo $dir->getFilename(); ?></a>
        </div>
      <?php endforeach; ?>
    <?php else: // None ?>
      <div class="cms_filebrowser_msg cms_filebrowser_empty">
        <p></p>
      </div>
    <?php endif; ?>
</div>

<div class="cms_filebrowser_list_files">
    <?php
    // Files
    $files = $kernel->finder()
      ->files()
      ->in($directory)
      ->depth(1)
      ->sortByName();
    
    // Has
    if($fileCount = count($files) > 1):
      foreach($files as $mFile):
        $file = new File($mFile);
      ?>
        <div class="cms_filebrowser_tile cms_filebrowser_file">
          <a href="<?php echo $file->getRelativePath(); ?>"><?php echo $file->getFilename(); ?></a>
        </div>
      <?php endforeach; ?>
    <?php else: // None ?>
      <div class="cms_filebrowser_msg cms_filebrowser_empty">
        <p>Directory is empty</p>
      </div>
    <?php endif; ?>
</div>

<div class="cms_filebrowser_upload">
  <?php echo $kernel->dispatchRequest('Filebrowser', 'new'); ?>
</div>