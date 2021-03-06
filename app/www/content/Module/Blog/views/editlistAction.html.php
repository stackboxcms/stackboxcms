
<div class="module_blog">
    <p>
        <a href="<?php echo $kernel->url(array('page' => $page->url, 'module_name' => 'blog_post', 'module_id' => $module->id, 'module_action' => 'new'), 'module'); ?>" class="cms_button">New Post</a>
    </p>
<?php
$table = $view->generic('datagrid');
$table->data($posts)
    ->column('Post', function($item) { return $item->title; })
    ->column('Status', function($item) { return $item->status; })
    ->column('Date Published', function($item) use($view) { return $view->toDate($item->date_published); })
    ->column('Edit', function($item) use($view, $page, $module) {
        return $view->link('edit', array('page' => $page->url, 'module_name' => 'blog_post', 'module_id' => $module->id, 'module_action' => 'edit', 'module_item' => $item->id), 'module_item');
    })
    ->column('Delete', function($item) use($view, $page, $module) {
        return $view->link('delete', array('page' => $page->url, 'module_name' => 'blog_post', 'module_id' => $module->id, 'module_action' => 'delete', 'module_item' => $item->id), 'module_item');
    });
echo $table->content();
?>
</div>
