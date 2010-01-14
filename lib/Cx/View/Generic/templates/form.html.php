<?php
$form = $this->helper('Form');
?>

<?php if($this->fields && count($fields) >0): ?>
<form>
  <?php foreach($fields as $fieldName => $fieldOpts): ?>
  <p>
  	<label><?php echo $fieldName; ?></label>
  	<?php echo $form->input($fieldOpts['type'], $fieldName); ?>
  </p>
  <?php endforeach; ?>
</form>
<?php endif; ?>