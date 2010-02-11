<?php
$form = $this->helper('Form');
$formMethod = strtolower(($this->method == 'GET' || $this->method == 'POST') ? $this->method : 'post');
$formMethodRest = ($formMethod == 'POST' && $this->method != 'POST') ? $this->method : false;
?>

<?php foreach($this->errors() as $fieldName => $fieldOpts): ?>

<?php endforeach; ?>

<?php if($this->fields && count($fields) >0): ?>
<form action="<?php echo $this->action; ?>" method="post">
  <dl class="cx_form">
  <?php foreach($fields as $fieldName => $fieldOpts): ?>
	<dt class="cx_form_label"><label><?php echo $fieldName; ?></label></dt>
	<dd class="cx_form_value cx_form_field_<?php echo strtolower($fieldOpts['type']); ?>">
	  <?php echo $form->input($fieldOpts['type'], $fieldName, $request->$fieldName); ?>
	</dd>
  <?php endforeach; ?>
  </dl>
  <div class="cx_form_hidden">
	<?php if($formMethodRest): ?>
	<input type="hidden" name="_method" value="<?php echo $formMethodRest; ?>" />
	<?php endif; ?>
  </div>
  <div class="cx_form_actions">
	<button type="submit" class="cx_action_primary">Save</button>
	<a href="#" class="cx_action_cancel">Cancel</a>
  </div>
</form>
<?php endif; ?>