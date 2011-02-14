
<div class="module_blog">
    <p><a href="<?php echo $kernel->url(array('page' => $this->page->url, 'module_name' => 'blog_post', 'module_id' => $module->id, 'module_action' => 'new'), 'module'); ?>">New Post</a></p>
<?php
$table = $view->generic('datagrid');
$table->data($posts)
    ->column('Post', function($item) { return $item->title; })
    ->column('Status', function($item) { return $item->status; })
    ->column('Date Published', function($item) { return $item->date_published; });
echo $table;
?>
</div>
