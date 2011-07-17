<?php
// Use generic TreeView to recursively display links
$tree = $view->generic('treeview');
$tree->data($pages)
    ->item(function($page) use($view) {
        return '<a href="' . $view->url(array('page' => ltrim($page->url, '/')), 'page') . '" title="' . $page->title . '">' . $page->title . '</a>';
    })
    ->itemChildren(function($page) {
        return $page->children;
    })
    ->levelMin($module->setting('level_min', 0))
    ->levelMax($module->setting('level_max', 0))
    ->filter(function($page) use($module) {
        // Setting: Show Homepage?
        if($page->isHomepage() && !$module->setting('show_homepage')) {
            return false;
        }

        // Page option: Hidden from navigation?
        if(!$page->isVisible()) {
            return false;
        }
    })
    // Custom root CSS class via module setting
    ->beforeItemSet(function() use($module, $tree) {
        if(0 == $tree->level()) {
            return "<ul class=\"" . $module->setting('css_ul_root', 'cms_nav') . "\">\n";   
        } else {
            return "<ul>\n";
        }
    })
    ->beforeItem(function($page) use($module) {
        return "<li class=\"page_" . $page->id . "\">\n";
    });
echo $tree->content();
