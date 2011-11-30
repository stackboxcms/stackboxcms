<?php
// Form URL to update settings with
$formUrl = $view->url(array(
  'module_name' => 'settings',
  'module_id' => (int) $module->id),
'module');
?>
<h2>Settings</h2>

<!-- tabs -->
<?php if(isset($settingsInit)): ?>
<form action="<?php echo $formUrl; ?>" method="POST">
  
  <ul id="tabs-m<?php echo $module->id; ?>-settings" class="tabs" data-tabs="tabs">
    <?php
    $i = 0;
    foreach($settingsInit as $group => $fields):
      ++$i;
    ?>
    <li class="<?php echo (1 === $i) ? 'active' : '' ?>"><a href="#tab-m<?php echo $module->id . '-' .$kernel->formatUrl($group); ?>"><?php echo $kernel->formatUnderscoreWord($group); ?></a></li>
    <?php endforeach; ?>
  </ul>


  <!-- tab content -->
  <div class="tab-content">
    <?php
    $i = 0;
    foreach($settingsInit as $group => $fields):
      ++$i;
    ?>
    <div id="tab-m<?php echo $module->id . '-' .$kernel->formatUrl($group); ?>" class="<?php echo (1 === $i) ? ' active' : '' ?>">
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
      <div class="clear"></div>
    </div>
    <?php endforeach; ?>
  </div>

  
  <div class="app_form_actions"> 
    <input type="hidden" name="target_module_name" value="<?php echo $module->name; ?>" />
    <button type="submit" class="app_action_primary">Save</button> 
    <a href="#" class="app_action_cancel">Cancel</a>
  </div> 
</form>
<?php endif; ?>