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

<?php if($this->fields && count($this->fields) >0): ?>
<form action="<?php echo $this->action; ?>" method="post">
  <ol class="app_form">
  <?php
  foreach($this->fields as $fieldName => $fieldOpts):
	$fieldLabel = isset($fieldOpts['title']) ? $fieldOpts['title'] : ucwords(str_replace('_', ' ', $fieldName));
	$fieldType = isset($fieldOpts['type']) ? $fieldOpts['type'] : 'string';
    ?>
	<li class="app_form_field app_form_field_<?php echo strtolower($fieldOpts['type']); ?>">
	  <label><?php echo $fieldLabel; ?></label></dt>
	  <span>
	  <?php
	  // Adjust field depending on field type
	  switch($fieldType) {
		case 'text':
		  echo $form->textarea($fieldName, $this->data($fieldName), array('rows' => 10, 'cols' => 60));
		break;
		
		case 'bool':
		case 'boolean':
		  echo $form->checkbox($fieldName, (int) $this->data($fieldName));
		break;
		
		case 'int':
		case 'integer':
		  echo $form->text($fieldName, $this->data($fieldName), array('size' => 10));
		break;
		
		case 'string':
		  echo $form->text($fieldName, $this->data($fieldName), array('size' => 40));
		break;
		
		case 'select':
		  $options = isset($fieldOpts['options']) ? $fieldOpts['options'] : array();
		  echo $form->select($fieldName, $options, $this->data($fieldName));
		break;
		
		default:
		  echo $form->input($fieldOpts['type'], $fieldName, $this->data($fieldName));
	  }
	  ?>
	  </span>
	</li>
  <?php endforeach; ?>
	<li class="app_form_hidden">
	  <?php if($formMethodRest): ?>
	  <input type="hidden" name="_method" value="<?php echo $formMethodRest; ?>" />
	  <?php endif; ?>
	</li>
	<li class="app_form_actions">
	  <button type="submit" class="app_action_primary">Save</button>
	  <a href="#" class="app_action_cancel">Cancel</a>
    </li>
  </ol>
</form>
<?php endif; ?>