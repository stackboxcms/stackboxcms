<?php
// Use generic TreeView to recursively display links
$tree = $view->generic('treeview');
$tree->data($pages)
    ->item(function($page) use($view) {
        return '<a href="' . $view->url(array('page' => ltrim($page->url, '/')), 'page') . '" title="' . $page->navigationTitle() . '">' . $page->navigationTitle() . '</a>';
    })
    ->itemChildren(function($page) {
        return $page->children;
    })
    ->levelMin((int) $module->setting('level_min', 0))
    ->levelMax((int) $module->setting('level_max', 0))
    ->filter(function($page) use($module, $currentPage) {
        // Filter pages based on section
        if('section' == $module->setting('type')) {
            // Section-based navigation
        }

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
        $cssActive = $page->is_in_path ? ' page_active' : '';
        return "<li class=\"page_" . $page->id . $cssActive . "\">\n";
    });
echo $tree->content();