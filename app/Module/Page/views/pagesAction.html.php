<div id="cms_pages">
<h2>Pages</h2>
<form action="<?php echo $kernel->url(array('page' => $page->url, 'action' => 'pages'), 'page_action'); ?>" method="POST">
<?php
// Use generic TreeView to recursively display links
$tree = $view->generic('treeview')
    ->data($pages)
    // Customise the <li> item display to include the page ID
    ->beforeItem(function($page) {
       return '<li id="pages_' . $page->id . '">'; 
    })
    // Page item template
    ->item(function($page) use($view) {
    ?>
      <div class="cms_pages_item">
        <?php echo $view->link($page->title, array('page' => ltrim($page->url, '/')), 'page'); ?>
        <input type="hidden" name="" value="" />
      </div>
    <?php
    })
    // Returns empty array if no children
    ->itemChildren(function($page) {
        return $page->children;
    });
echo $tree->content();
?>
</form>
</div>

<?php
// Add tree JS
$asset = $view->helper('Asset');
echo $asset->script('jquery.ui.nestedSortable.js');
?>
<script type="text/javascript">
$('#cms_pages ul').nestedSortable({
    forcePlaceholderSize: true,
    handle: 'div.cms_pages_item',
    helper: 'clone',
    listType: 'ul',
    items: 'li',
    maxLevels: 0,
    opacity: 0.8,
    placeholder: 'cms_pages_placeholder', // CSS class
    revert: 250,
    tabSize: 25,
    tolerance: 'pointer',
    toleranceElement: '> div'
});

// Custom function to build sorted list
function serializeNestedSet(items, parentId) {
    var outStr = "";
    if(items.is("ul")) {
        items = items.children("li").not(".ui-sortable-helper");
    }
    if(items.length > 0) {
        items.each(function() {
            var itemId = this.id;
            outStr += "&item["+parentId+"][]="+itemId+"";
            outStr += serializeNestedSet($(this).children('ul'), itemId);
        });
    }
    return outStr;
}

$('#cms_pages form').submit(function() {
   var pageData = $('#cms_pages ul').nestedSortable('serialize');
   console.log(pageData);
   console.log(serializeNestedSet($('#cms_pages ul'), 0));
});
</script>