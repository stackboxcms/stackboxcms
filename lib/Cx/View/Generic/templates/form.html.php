<?php
$form = $this->helper('Form');
?>

<?php if($this->fields && count($fields) >0): ?>
<form action="" method="post">
  <dl class="cx_form">
  <?php foreach($fields as $fieldName => $fieldOpts): ?>
	<dt class="cx_form_label"><label><?php echo $fieldName; ?></label></dt>
	<dd class="cx_form_value cx_form_field_<?php echo strtolower($fieldOpts['type']); ?>">
	  <?php echo $form->input($fieldOpts['type'], $fieldName, $request->$fieldName); ?>
	</dd>
  <?php endforeach; ?>
  </dl>
  <div class="cx_form_hidden">
	<input type="hidden" name="_method" value="<?php echo $this->method; ?>" />
  </div>
  <div class="cx_form_actions">
	<button type="submit" class="cx_action_primary">Save</button>
	<a href="#" class="cx_action_cancel">Cancel</a>
  </div>
</form>
<?php endif; ?>