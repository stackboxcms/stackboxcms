<?php
// Form URL to update settings with
$formUrl = $view->url(array(
	'module_name' => 'page_module_settings',
	'module_id' => 0),
'module');
?>
<h2>Settings</h2>

<form action="<?php echo $formUrl; ?>" method="POST">
<?php foreach($settingsInit as $group => $fields): ?>
  <h3><?php echo $kernel->formatUnderscoreWord($group); ?></h3>

  <?php
  // @todo Need a way to display fields without <form> tags - this is in effect many forms that will make up a single, larger form
  $form = $view->generic('Form')
  	->fields($fields)
  	->formTags(false)
  	->submit(false);
  echo $form->content();
  ?>
<?php endforeach; ?>
  <input type="hidden" name="target_module_id" value="<?php echo (int) $module->id; ?>" />
  <button type="submit" class="app_action_primary">Save</button>
</form>