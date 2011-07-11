<?php
// Use generic TreeView to recursively display links
$tree = $view->generic('treeview')
    ->data($pages)
    ->item(function($page) use($view) {
        return '<a href="' . $view->url(array('page' => ltrim($page->url, '/')), 'page') . '" title="' . $page->title . '">' . $page->title . '</a>';
    })
    ->itemChildren(function($page) {
        return $page->children;
    })
    ->levelMin($module->setting('level_min', 0)-1)
    ->levelMax($module->setting('level_max', 99)-1)
    ->filter(function($page) use($module) {
        // Setting: Show Homepage
        if($page->url == '/' && !$module->setting('show_homepage', true)) {
            return false;
        }
    });
echo $tree->content();
