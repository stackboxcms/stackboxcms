<?php
use Module\Filebrowser\File;

$request = $kernel->request();
?>

<div class="cms_filebrowser">
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
    
    // Generic cellgrid layout
    $grid = $view->generic('cellgrid');
    $grid->data($files)
      ->columns(6)
      ->cell(function($mFile) {
        // Get into File object
        $file = new File($mFile);
      ?>
        <?php if($file->isImage()): // Image ?>
        <div class="cms_filebrowser_tile cms_filebrowser_image cms_filebrowser_extension_<?php echo $file->getExtension(); ?>">
          <nav class="cms_filebrowser_hover_nav">
            <ul>
              <li><a href="<?php echo $file->getSizeUrl(100, 100); ?>">Thumbnail (100x100)</a></li>
              <li><a href="<?php echo $file->getSizeUrl(250, 250); ?>">Small (250x250)</a></li>
              <li><a href="<?php echo $file->getSizeUrl(400, 400); ?>">Medium (400x400)</a></li>
              <li><a href="<?php echo $file->getSizeUrl(600, 600); ?>">Large (600x600)</a></li>
              <li><a href="<?php echo $file->getSizeUrl(1024, 768); ?>">X-Large (1024x768)</a></li>
              <li><a href="<?php echo $file->getUrl(); ?>">Full Size</a></li>
            </ul>
          </nav>
          <a href="<?php echo $file->getUrl(); ?>"><img src="<?php echo $file->getSizeUrl(100, 100); ?>" alt="<?php echo $file->getFilename(); ?>" /></a>
        </div>
        <?php else: // File ?>
        <div class="cms_filebrowser_tile cms_filebrowser_file cms_filebrowser_extension_<?php echo $file->getExtension(); ?>">
          <a href="<?php echo $file->getUrl(); ?>"><?php echo $file->getFilename(); ?></a>
        </div>
        <?php endif; ?>
    <?php
      });
    echo $grid->content();
    ?>
</div>

<div class="cms_filebrowser_upload">
  <?php echo $kernel->dispatchRequest('Filebrowser', 'new'); ?>
</div>

<!-- Image sizes hover menu -->
<script type="text/javascript">
  // Bind click events to use CKEditor callback
  $('div.cms_filebrowser_image nav').hide();
  $('div.cms_filebrowser_image').hover(function (e) {
    $(this).find('nav').show();  
  }, function() {
    $(this).find('nav').hide();
  });
</script>

<?php
if($request->get('CKEditor')):
  $callback = $request->get('CKEditorFuncNum');
?>
  <!-- CKEditor callback integration -->
  <script type="text/javascript">
  // Bind click events to use CKEditor callback
  $('a').bind('click',function (e) {
      window.opener.CKEDITOR.tools.callFunction('<?php echo $callback; ?>', this.href);
      window.close();
      return false;
  });
  </script>
<?php endif; ?>
</div>