
<div class="module_blog">
    <p><a href="<?php echo $this->kernel->url(array('page' => $this->page->url, 'module_name' => 'blog_post', 'module_id' => $this->module->id, 'module_action' => 'new'), 'module'); ?>">New Post</a></p>
<?php
$table = new \Alloy\View\Generic\Datagrid('datagrid');
$table->data($this->posts)
    ->column('Post', function($item, $view) { return $item->title; })
    ->column('Status', function($item, $view) { return $item->status; })
    ->column('Date Published', function($item, $view) { return $item->date_published; });
echo $table;
?>
</div>
