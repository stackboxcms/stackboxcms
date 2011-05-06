<h2>Settings</h2>

<?php foreach($settingsInit as $group => $fields): ?>
  <h3><?php echo $kernel->formatUnderscoreWord($group); ?></h3>

  <?php
  // @todo Need a way to display fields without <form> tags - this is in effect many forms that will make up a single, larger form
  $form = $view->generic('Form')
  	->fields($fields);
  echo $form->content();
  ?>
<?php endforeach; ?>