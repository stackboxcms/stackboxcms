<?php
$asset = $view->helper('Asset');
$templateHead = $view->head();

// If page title has been set by sub-template
if($title = $templateHead->title()) {
	$title .= " - StackboxCMS";
} else {
	$title = "StackboxCMS";
}

// Admin toolbar, javascript, styles, etc.
$templateHead->script($kernel->config('cms.url.assets') . 'jquery-1.5.1.min.js');
$templateHead->script($kernel->config('cms.url.assets') . 'jquery-ui-1.8.11.min.js');

// Setup javascript variables for use
$templateHead->prepend('<script type="text/javascript">
var cms = {
    config: {url: "' . $kernel->config('url.root') . '", url_assets: "' . $kernel->config('url.assets') . '", url_assets_admin: "' . $kernel->config('cms.url.assets_admin') . '"},
    editor: {
        fileUploadUrl: "' . $kernel->url(array('action' => 'upload'), 'filebrowser', array('type' => 'file')) . '",
        imageUploadUrl: "' . $kernel->url(array('action' => 'upload'), 'filebrowser', array('type' => 'image')) . '",
        fileBrowseUrl: "' . $kernel->url(array('action' => 'files'), 'filebrowser') . '",
        imageBrowseUrl: "' . $kernel->url(array('action' => 'images'), 'filebrowser') . '"
    }
};
</script>' . "\n");
$templateHead->script($kernel->config('cms.url.assets_admin') . 'scripts/ckeditor/ckeditor.js');
$templateHead->script($kernel->config('cms.url.assets_admin') . 'scripts/ckeditor/adapters/jquery.js');
$templateHead->script($kernel->config('cms.url.assets_admin') . 'scripts/cms_admin.js');
$templateHead->stylesheet('jquery-ui/aristo/aristo.css');
$templateHead->stylesheet($kernel->config('cms.url.assets_admin') . 'styles/cms_admin.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo $title; ?></title>
    <?php echo $templateHead; ?>
</head>
<body class="cms_ui">
    
    <?php
    // Display errors
    if($errors = $view->errors()):
    ?>
      <p><b>ERRORS:</b></p>
      <ul>
      <?php foreach($errors as $field => $fieldErrors): ?>
      	<?php foreach($fieldErrors as $error): ?>
      		<li><?php echo $error; ?></li>
      	<?php endforeach; ?>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php echo $content; ?>
    
</body>
</html>