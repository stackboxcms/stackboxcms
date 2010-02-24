<?php
//$this->head()->scriptTag('assets/admin/scripts/jquery.event.drag-1.5.min.js');
//$this->head()->scriptTag('assets/admin/scripts/jquery.event.drop-1.2.min.js');
?>
<div id="cx_admin_bar">
  <div id="cx_admin_bar_inside">
	<ul>
	  <li><a href="<?php echo $cx->url('index_action', array('action' => 'new')); ?>">New Page</a></li>
	</ul>
  </div>
</div>
<div id="cx_admin_modules" class="cx_admin_pane">
	<h2>Modules</h2>
	<div class="cx_admin_pane_content">
		<div class="cx_admin_modules_module"><a href="#">Text Module</a></div>
  </div>
</div>
<div id="cx_modal"><div id="cx_modal_content"></div></div>