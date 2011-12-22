<?php
$slideshowId = 'module_slideshow_' . $module->id;
$assetsUrl = $kernel->config('url.root') . $kernel->config('cms.dir.modules') . 'Module/' . $module->name . '/assets/';

$sWidth = (int) $module->setting('width', 600);
$sHeight = (int) $module->setting('height', 375);
?>


<div id="<?php echo $slideshowId; ?>" class="<?php echo $module->setting('slide_container_class'); ?>">
    <div class="module_slideshow_slides">
        <?php foreach($items as $item): ?>
        <div class="module_slideshow_item">
            <?php if($item->link): ?><a href="<?php echo $item->link; ?>"><?php endif; ?><img src="<?php echo $item->url; ?>" alt="<?php echo $item->caption; ?>" /><?php if($item->link): ?></a><?php endif; ?>
            <?php if($item->caption): ?>
            <div class="module_slideshow_item_caption"><p><?php echo $item->caption; ?></p></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="clear"></div>
</div>


<script type="text/javascript">
// @link http://jquery.malsup.com/cycle/lite/
$('#<?php echo $slideshowId; ?> .module_slideshow_slides').cycle({ 
    delay:  <?php echo (int) $module->setting('slide_delay', 5000); ?>, 
    speed:  <?php echo (int) $module->setting('slide_speed', 2000); ?>,
    pause:  true,
    width: <?php echo $sWidth; ?>,
    height: <?php echo $sHeight; ?>
});
</script>
<style type="text/css">
#<?php echo $slideshowId; ?> {
    overflow: hidden;
    background-color: <?php echo $module->setting('background_color', 'transparent'); ?>;
}
#<?php echo $slideshowId; ?> .module_slideshow_item img {
    max-height: <?php echo $sHeight; ?>;
}
<?php
$align = $module->setting('alignment', 'left');
if('center' == $align): ?>
    #<?php echo $slideshowId; ?> .module_slideshow_slides {
        text-align: center;
        margin: 0 auto;
        width: <?php echo $sWidth; ?>;
        height: <?php echo $sHeight; ?>;
    }
<?php elseif('right' == $align): ?>
    #<?php echo $slideshowId; ?> .module_slideshow_slides {
        float: right;
    }
<?php endif; ?>
</style>

<?php
// Add javsacript for slideshow to page
$asset = $view->helper('Asset');
$view->head()->script($assetsUrl . '/scripts/jquery.cycle.lite.min.js');
?>