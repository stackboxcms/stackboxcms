<?php
use Module\Filebrowser\File;
?>
<h2>Directories</h2>
<div>
    <?php
    // Directories
    $moduleDirsPath = $kernel->config('cms.path.files');
    $moduleDirs = $kernel->finder()
      ->directories()
      ->in($moduleDirsPath)
      ->sortByName();
    foreach($moduleDirs as $mDir):
      $dir = new File($mDir);
    ?>
      <div class="cms_filebrowser_tile cms_filebrowser_dir">
        <a href="<?php echo $dir->getFilepath(); ?>"><?php echo $dir->getFilename(); ?></a>
      </div>
    <?php endforeach; ?>
</div>