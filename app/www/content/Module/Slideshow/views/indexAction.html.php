<?php
$slideshowId = 'module_slideshow_' . $module->id;
$assetsUrl = $kernel->config('url.root') . $kernel->config('cms.dir.modules') . 'Module/' . $module->name . '/assets/';
?>

<div id="<?php echo $slideshowId; ?>" class="module_slideshow_wrapper">
    <?php foreach($items as $item): ?>
    <div class="module_slideshow_item">
        <?php if($item->link): ?><a href="<?php echo $item->link; ?>"><?php endif; ?><img src="<?php echo $item->url; ?>" alt="<?php echo $item->caption; ?>" /><?php if($item->link): ?></a><?php endif; ?>
        <?php if($item->caption): ?>
        <div class="module_slideshow_item_caption"><p><?php echo $item->caption; ?></p></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<script type="text/javascript">
// @link http://jquery.malsup.com/cycle/lite/
$('#<?php echo $slideshowId; ?>').cycle({ 
    delay:  <?php echo (int) $module->setting('slide_delay', 5000); ?>, 
    speed:  <?php echo (int) $module->setting('slide_speed', 2000); ?>,
    pause:  true,
    height: <?php echo (int) $module->setting('height', 300); ?>,
    before: function() {
        //console.log(this);
    }
});
</script>

<?php
// Add javsacript for slideshow to page
$asset = $view->helper('Asset');
$view->head()->script($assetsUrl . '/scripts/jquery.cycle.lite.min.js');
?>