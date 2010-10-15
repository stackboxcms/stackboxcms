
<div class="module_blog">
<?php
$view = $this; // Closure scoping
$table = new \Alloy\View\Generic\Datagrid('datagrid');
$table->data($this->posts)
  ->column('Post', 'title', function($item) use($view) { return ""; })
  ->column('date_published');
echo $table;
?>
</div>