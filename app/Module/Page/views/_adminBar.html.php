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
    // NOT this place for this... Should move it somewhere better soon...
    $site = $kernel->site();
    $moduleConfig = function(\SplFileInfo $file) use($site, $kernel) {
      // Require config file
      $cfg = require $file->getRealPath();

      // Set standardized config values based on SplFileInfo object
      $cfg['dirname'] = strrchr($file->getPath(), '/');
      $cfg['path_dir'] = $file->getRealPath();
      $cfg['is_site'] = !(false === strpos($cfg['path_dir'], $site->dir()));
      if($cfg['is_site']) {
        $cfg['url_dir'] = $site->url() . $kernel->config('cms.dir.modules') . 'Module/Site' . $cfg['dirname'];
      } else {
        $cfg['url_dir'] = $kernel->config('url.root') . $kernel->config('cms.dir.modules') . 'Module' . $cfg['dirname'];
      }
      return $cfg;
    };

    // Module Files
    $moduleDirsPath = $kernel->config('cms.path.modules');
    $moduleDirs = $kernel->finder()
      ->files()
      ->name('_module.php')
      ->in($kernel->site()->moduleDirs())
      ->sortByName();
    foreach($moduleDirs as $mFile):
      $mCfg = $moduleConfig($mFile);
    ?>
      <div rel="cms_module_tile_<?php echo $mCfg['name']; ?>" class="cms_module_tile">
        <h3><?php echo $mCfg['name']; ?></h3>
        <div class="cms_module_tile_icon"><img src="<?php echo $mCfg['url_dir']; ?>/assets/images/icon.png" alt="<?php echo $mCfg['name']; ?> Module" width="32" height="32" /></div>
      </div>
    <?php endforeach; ?>
    <div class="clear"></div>
  </div>
</div>
<div id="cms_ui_helper" class="cms_ui"></div>
<div id="cms_modal" class="cms_ui"><div id="cms_modal_content">Loading...</div></div>