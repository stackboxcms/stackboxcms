<!-- Admin top bar -->
<div id="cms_admin_bar" class="topbar" data-dropdown="dropdown">
  <div class="topbar-inner">
    <!-- page menu -->
    <ul class="nav">
      <li><a id="cms_admin_bar_editPage" class="cms_admin_bar_edit" href="#">Edit Page</a></li>
      <li><a id="cms_admin_bar_addContent" class="cms_admin_bar_edit" href="#">Add Module</a></li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle">Site</a>
        <ul class="dropdown-menu">
          <li><a href="<?php echo $view->url(array('action' => 'new'), 'index_action'); ?>" rel="modal">New Page</a></li>
          <li><a href="<?php echo $view->url(array('action' => 'pages'), 'index_action'); ?>" rel="modal">Pages (Sitemap)</a></li>
        </ul>
      </li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle">Page</a>
        <ul class="dropdown-menu">
          <?php if('/' == $page->url): // ugly hack until routes are fixed for good ?>
            <li><a href="<?php echo $view->url(array('action' => 'edit'), 'index_action'); ?>" rel="modal">Edit Page</a></li>
          <?php else: ?>
            <li><a href="<?php echo $view->url(array('page' => $page->url, 'action' => 'edit'), 'page_action'); ?>" rel="modal">Edit Page</a></li>
            <li><a href="<?php echo $view->url(array('page' => $page->url, 'action' => 'delete'), 'page_action'); ?>" rel="modal">Delete Page</a></li>
          <?php endif; ?>
          <li><a href="<?php echo $view->url(array('page' => $page->url, 'module_id' => $page->id, 'module_name' => 'page', 'module_action' => 'settings'), 'module'); ?>" rel="modal">Page Settings</a></li>
        </ul>
      </li>
    </ul>
    <!-- user menu -->
    <ul id="cms_admin_nav_user" class="nav secondary-nav">
      <li><a href="<?php echo $kernel->url(array('page' => $page->url, 'module_name' => 'user', 'module_id' => 0, 'module_action' => 'editlist'), 'module'); ?>" rel="modal">Users</a></li>
      <li><a href="<?php echo $kernel->url('logout'); ?>">Logout</a></li>
    </ul>
    <div class="clear"></div>
  </div>
</div>


<!-- Modules user can add in regions -->
<div id="cms_admin_modules" class="cms_ui">
  <div class="cms_ui_pane_content">
    <?php
    // Module Files
    $moduleDirsPath = $kernel->config('cms.path.modules');
    $moduleDirs = $kernel->finder()
      ->directories()
      ->in($kernel->site()->moduleDirs())
      ->depth(1)
      ->sortByName();
    foreach($moduleDirs as $mDir):
      $moduleAssetsUrl = $kernel->config('url.root') . $kernel->config('cms.dir.modules') . 'Module/' . $mDir->getFilename() . '/assets/';
    ?>
      <div rel="cms_module_tile_<?php echo $mDir->getFilename(); ?>" class="cms_module_tile">
        <h3><?php echo $mDir->getFilename(); ?></h3>
        <div class="cms_module_tile_icon"><img src="<?php echo $moduleAssetsUrl; ?>/images/icon.png" alt="<?php echo $mDir->getFilename(); ?> Module" width="32" height="32" /></div>
      </div>
    <?php endforeach; ?>
    <div class="clear"></div>
  </div>
</div>
<div id="cms_ui_helper" class="cms_ui"></div>
<div id="cms_modal" class="cms_ui"><div id="cms_modal_content">Loading...</div></div>