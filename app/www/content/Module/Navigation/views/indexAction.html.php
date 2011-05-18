<?php
// Use generic TreeView to recursively display links
$tree = $view->generic('treeview')
    ->data($pages)
    ->item(function($page) use($view) {
        return '<a href="' . $view->url(array('page' => ltrim($page->url, '/')), 'page') . '" title="' . $page->title . '">' . $page->title . '</a>';
    })
    ->itemChildren(function($page) {
        return $page->children;
    });
echo $tree->content();
?>