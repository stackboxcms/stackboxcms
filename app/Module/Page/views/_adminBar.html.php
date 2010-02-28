<div id="cx_admin_bar" class="cx_ui">
  <div id="cx_admin_bar_inside">
	<ul>
	  <li><a href="<?php echo $this->kernel->url('index_action', array('action' => 'new')); ?>">New Page</a></li>
	  <?php if('/' == $this->page->url): // ugly hack until routes are fixed for good ?>
		<li><a href="<?php echo $this->kernel->url('index_action', array('action' => 'edit')); ?>">Edit Page</a></li>
	  <?php else: ?>
		<li><a href="<?php echo $this->kernel->url('page_action', array('page' => $this->page->url, 'action' => 'edit')); ?>">Edit Page</a></li>
	  <?php endif; ?>
	</ul>
  </div>
</div>
<div id="cx_admin_modules" class="cx_ui cx_ui_pane">
  <h2>Modules</h2>
  <div class="cx_ui_pane_content">
	<div id="cx_module_tile_Text" class="cx_module_tile">Text Module</div>
	<div id="cx_module_tile_Navigation" class="cx_module_tile">Navigation Module</div>
  </div>
  <div class="cx_ui_pane_info">
	<p>Drag and drop modules into content regions on the page.</p>
  </div>
</div>
<div id="cx_modal" class="cx_ui"><div id="cx_modal_content"></div></div>