
<div class="module_blog">
<?php
$view = $this; // Closure scoping
$table = new \Alloy\View\Generic\Datagrid('datagrid');
$table->data($this->posts)
    ->column('Post', function($item, $view) { return $item->title; })
    ->column('Status', function($item, $view) { return $item->status; })
    ->column('Date Published', function($item, $view) { return $item->date_published; });
echo $table;
?>
</div>