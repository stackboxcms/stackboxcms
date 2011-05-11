<?php
// Form URL to update settings with
$formUrl = $view->url(array(
  'module_name' => 'page_module_settings',
  'module_id' => (int) $module->id),
'module');
?>
<h2>Settings</h2>

<?php if(isset($settingsInit)): ?>
<form action="<?php echo $formUrl; ?>" method="POST">
<?php foreach($settingsInit as $group => $fields): ?>
  <h3><?php echo $kernel->formatUnderscoreWord($group); ?></h3>

  <?php
  // Determine which settings belong to this form
  $fieldData = array_intersect_key($settings, $fields);

  // Render form fields without <form> tags or submit buttons
  $form = $view->generic('Form')
    ->fields($fields)
    ->data($fieldData)
    ->formTags(false)
    ->submit(false);
  echo $form->content();
  ?>
<?php endforeach; ?>
    <div class="app_form_actions"> 
      <button type="submit" class="app_action_primary">Save</button> 
      <a href="#" class="app_action_cancel">Cancel</a>
    </div> 
</form>
<?php endif; ?>