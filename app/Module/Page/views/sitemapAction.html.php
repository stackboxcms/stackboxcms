<?php
// Use generic TreeView to recursively display links
$tree = $view->generic('treeview')
    ->data($pages)
    ->item(function($page) use($view) {
        return $page->title;
    })
    ->itemChildren(function($page) {
        return $page->children;
    });
echo $tree->content();
?>