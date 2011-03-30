<?php
use Module\Filebrowser\File;

$request = $kernel->request();
?>

<div class="cms_filebrowser_list_dirs">
  <?php
  // Directories
  $dirs = $kernel->finder()
    ->directories()
    ->in($directory)
    ->depth(0)
    ->notName('_*') // Hide dirs that begin with underscore (_size)
    ->sortByName();
    
    foreach($dirs as $mDir):
      $dir = new File($mDir);
    ?>
      <div class="cms_filebrowser_tile cms_filebrowser_dir">
        <a href="<?php echo $dir->getPathname(); ?>"><?php echo $dir->getFilename(); ?></a>
      </div>
    <?php endforeach; ?>
</div>

<div class="cms_filebrowser_list_files">
  <?php
  // Files
  $files = $kernel->finder()
    ->files()
    ->in($directory)
    ->depth(0)
    ->notName('index.html') // Hide index file placeholder
    ->notName('.*') // Hide files that begin with dot, like .DS_Store
    ->sortByName();
    
    foreach($files as $mFile):
      $file = new File($mFile);
    ?>
      <?php if($file->isImage()): // Image ?>
      <div class="cms_filebrowser_tile cms_filebrowser_image cms_filebrowser_extension_<?php echo $file->getExtension(); ?>">
        <a href="<?php echo $file->getUrl(); ?>"><img src="<?php echo $file->getSizeUrl(100, 100); ?>" alt="<?php echo $file->getFilename(); ?>" /></a>
      </div>
      <?php else: // File ?>
      <div class="cms_filebrowser_tile cms_filebrowser_file cms_filebrowser_extension_<?php echo $file->getExtension(); ?>">
        <a href="<?php echo $file->getUrl(); ?>"><?php echo $file->getFilename(); ?></a>
      </div>
      <?php endif; ?>
    <?php endforeach; ?>
</div>

<div class="cms_filebrowser_upload">
  <?php echo $kernel->dispatchRequest('Filebrowser', 'new'); ?>
</div>

<?php
if($request->get('CKEditor')):
  $callback = $request->get('CKEditorFuncNum');
?>
  <!-- CKEditor callback integration -->
  <script type="text/javascript">
  // Bind click events to use CKEditor callback
  var links = document.getElementsByTagName("a");
  var link = '';
  for(var i = 0; i < links.length; i++) {
    //alert("FOUND LINKS: " + links.length);
    link = links.item(i);
    // Set onClick property
    link.addEventListener('click',function (e) {
      this.style.backgroundColor = 'yellow';
      window.opener.CKEDITOR.tools.callFunction('<?php echo $callback; ?>', this.href);
      window.close();
      return false;
    }, false);
  }
  </script>
<?php endif; ?>