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
    ->sortByName();
    
    foreach($files as $mFile):
      $file = new File($mFile);
    ?>
      <div class="cms_filebrowser_tile cms_filebrowser_file">
        <a href="<?php echo $file->getUrl(); ?>"><?php echo $file->getFilename(); ?></a>
      </div>
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