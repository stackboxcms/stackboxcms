<div id="cms_admin_bar" class="cms_ui">
  <a id="cms_admin_bar_addContent" href="#"><span>Add Module</span></a>
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
<div id="cms_admin_modules" class="cms_ui cms_ui_pane">
  <h2>Modules</h2>
  <div class="cms_ui_pane_content">
    <!-- @todo: Make this dynamically generted from installed modules -->
    <div id="cms_module_tile_Text" class="cms_module_tile">Text Module</div>
    <div id="cms_module_tile_Code" class="cms_module_tile">Code Module</div>
    <div id="cms_module_tile_Navigation" class="cms_module_tile">Navigation Module</div>
    <div id="cms_module_tile_Blog" class="cms_module_tile">Blog Module</div>
  </div>
  <div class="cms_ui_pane_info">
    <p>Drag and drop modules into content regions on the page.</p>
  </div>
</div>
<div id="cms_modal" class="cms_ui"><div id="cms_modal_content">Loading...</div></div>