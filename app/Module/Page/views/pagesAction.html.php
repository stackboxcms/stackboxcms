<div id="cms_pages">

<h2>Pages</h2>
<p>Drag &amp; drop pages to re-arrange them. Changes will not be permanent until you click 'Save'.</p>

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
        <input type="hidden" name="pages[<?php echo $page->id; ?>]" value="<?php echo (int) $page->parent_id; ?>" class="page_parent" />
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
    toleranceElement: '> div',
    stop: function(event, ui) {
        // Update form elements with correct parent_id's
        var pageData = $('#cms_pages ul').nestedSortable('toArray');
        $.each(pageData, function(i, item) {
            // Skip returned items that are not valid pages
            if(isNaN(parseInt(item.item_id))) {
                return;
            }

            // Correct odd naming convention...
            if("root" == item.parent_id) {
                item.parent_id = 0;
            }

            // Set value back in page tree input element
            $('#pages_' + item.item_id + ' .page_parent').val(item.parent_id);
        });
    }
});
</script>