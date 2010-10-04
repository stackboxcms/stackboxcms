
<div class="module_blog">
<?php
$table = new Alloy_View_Generic_Datagrid('datagrid');
/*
$table->data($this->posts)
  ->column('Post', 'title', function($item) use($this) { return ""; })
  ->column('date_published');
*/
$view = $this;
$test = function($var) use($view) {
	return $var;
};
echo $test('blah');
?>
</div>