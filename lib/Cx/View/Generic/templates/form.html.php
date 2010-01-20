<?php
$form = $this->helper('Form');
?>

<?php if($this->fields && count($fields) >0): ?>
<form action="" method="post">
  <dl class="cx-form-layout">
  <?php foreach($fields as $fieldName => $fieldOpts): ?>
	<dt class="cx-form-label"><label><?php echo $fieldName; ?></label></dt>
	<dd class="cx-form-value cx-form-field-<?php echo strtolower($fieldOpts['type']); ?>">
	  <?php echo $form->input($fieldOpts['type'], $fieldName, $request->$fieldName); ?>
	</dd>
  <?php endforeach; ?>
  </dl>
  <div class="cx-form-hidden">
	<input type="hidden" name="_method" value="<?php echo $this->method; ?>" />
  </div>
  <div class="cx-form-actions">
	<button type="submit" class="cx-action-primary">Save</button>
	<a href="#" class="cx-action-cancel">Cancel</a>
  </div>
</form>
<?php endif; ?>