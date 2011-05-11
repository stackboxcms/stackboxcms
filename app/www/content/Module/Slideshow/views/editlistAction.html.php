
<div class="module_slideshow">
    <p><a href="<?php echo $kernel->url(array('page' => $page->url, 'module_name' => 'slideshow', 'module_id' => $module->id, 'module_action' => 'new'), 'module'); ?>" class="cms_button">New Image</a></p>
<?php
$table = $view->generic('datagrid');
$table->data($items)
    ->column('Image', function($item) { return $item->url; })
    ->column('Caption', function($item) { return $item->caption; })
    ->column('Date Created', function($item) use($view) { return $view->toDate($item->date_published); })
    ->column('Edit', function($item) use($view, $page, $module) {
        return $view->link('Edit', array('page' => $page->url, 'module_name' => 'slideshow', 'module_id' => $module->id, 'module_action' => 'edit', 'module_item' => $item->id), 'module_item');
    });
echo $table->content();
?>
</div>
