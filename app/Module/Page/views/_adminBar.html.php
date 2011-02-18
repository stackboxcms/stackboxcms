<div id="cms_admin_bar" class="cms_ui">
  <div id="cms_admin_bar_primary">
    <ul>
      <li><a id="cms_admin_bar_editPage" href="#"><span>Edit Page</span></a></li>
      <li><a id="cms_admin_bar_addContent" href="#"><span>Add Module</span></a></li>
    </ul>
  </div>
  <div id="cms_admin_bar_inside">
    <ul>
      <li><a href="<?php echo $kernel->url(array('action' => 'new'), 'index_action'); ?>" rel="modal">New Page</a></li>
      <?php if('/' == $this->page->url): // ugly hack until routes are fixed for good ?>
        <li><a href="<?php echo $kernel->url(array('action' => 'edit'), 'index_action'); ?>" rel="modal">Edit Page</a></li>
      <?php else: ?>
        <li><a href="<?php echo $kernel->url(array('page' => $this->page->url, 'action' => 'edit'), 'page_action'); ?>" rel="modal">Edit Page</a></li>
        <li><a href="<?php echo $kernel->url(array('page' => $this->page->url, 'action' => 'delete'), 'page_action'); ?>" rel="modal">Delete Page</a></li>
      <?php endif; ?>
      <li><a href="<?php echo $kernel->url('logout'); ?>">Logout</a></li>
    </ul>
    <div class="clear"></div>
  </div>
</div>
<div id="cms_admin_modules" class="cms_ui">
  <h2>Modules</h2>
  <div class="cms_ui_pane_content">
    <?php
    // Module Files
    $moduleDirsPath = $kernel->config('stackbox.path.modules');
    $moduleDirs = $kernel->finder()
      ->directories()
      ->in($moduleDirsPath)
      ->depth(1)
      ->sortByName();
    foreach($moduleDirs as $mDir): ?>
      <div id="cms_module_tile_<?php echo $mDir->getFilename(); ?>" class="cms_module_tile"><?php echo $mDir->getFilename(); ?> Module</div>
    <?php endforeach; ?>
  </div>
  <div class="cms_ui_pane_info">
    <p>Drag and drop modules into content regions on the page.</p>
  </div>
</div>
<div id="cms_modal" class="cms_ui"><div id="cms_modal_content">Loading...</div></div>