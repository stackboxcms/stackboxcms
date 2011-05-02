
<div class="module_user">
    <p><a href="<?php echo $kernel->url(array('page' => $page->url, 'module_name' => 'user', 'module_id' => (int) $module->id, 'module_action' => 'new'), 'module'); ?>">New User</a></p>
<?php
$table = $view->generic('datagrid');
$table->data($users)
    ->column('User', function($item) { return $item->username; })
    ->column('Email', function($item) { return $item->email; })
    ->column('Admin', function($item) { return ($item->isAdmin()) ? 'yes' : '-'; })
    ->column('Date Created', function($item) use($view) { return $view->toDate($item->date_created); })
    ->column('Edit', function($item) use($view, $page, $module) {
        return $view->link('Edit', array('page' => $page->url, 'module_name' => 'user', 'module_id' => (int) $module->id, 'module_action' => 'edit', 'module_item' => $item->id), 'module_item');
    });
echo $table->content();
?>
</div>
