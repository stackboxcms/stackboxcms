<?php
$form = $this->helper('Form');
$formMethod = strtolower(($this->method == 'GET' || $this->method == 'POST') ? $this->method : 'post');
$formMethodRest = ($formMethod == 'POST' && $this->method != 'POST') ? $this->method : false;
?>

<?php if($this->errors()): ?>
<ul class="app_form_errors">
<?php foreach($this->errors() as $field => $errors): ?>
	<?php foreach($errors as $error): ?>
		<li><?php echo $field; ?>: <?php echo $error; ?></li>
	<?php endforeach; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if($this->fields && count($fields) >0): ?>
<form action="<?php echo $this->action; ?>" method="post">
  <dl class="app_form">
  <?php foreach($fields as $fieldName => $fieldOpts): ?>
	<dt class="app_form_label"><label><?php echo $fieldName; ?></label></dt>
	<dd class="app_form_value app_form_field_<?php echo strtolower($fieldOpts['type']); ?>">
	  <?php echo $form->input($fieldOpts['type'], $fieldName, $request->$fieldName); ?>
	</dd>
  <?php endforeach; ?>
  </dl>
  <div class="app_form_hidden">
	<?php if($formMethodRest): ?>
	<input type="hidden" name="_method" value="<?php echo $formMethodRest; ?>" />
	<?php endif; ?>
  </div>
  <div class="app_form_actions">
	<button type="submit" class="app_action_primary">Save</button>
	<a href="#" class="app_action_cancel">Cancel</a>
  </div>
</form>
<?php endif; ?>