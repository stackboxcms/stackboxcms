<?php
$slideshowId = 'slideshow_' . $module->id;
$assetsUrl = $kernel->config('url.root') . $kernel->config('cms.dir.modules') . 'Module/' . $module->name . '/assets/';
?>

<div id="<?php echo $slideshowId; ?>" class="slideshow_wrapper">
  <div class="slideshow_container">
<?php foreach($items as $item): ?>
    <div class="slideshow_item">
        <?php if($item->link): ?><a href="<?php echo $item->link; ?>"><?php endif; ?><img src="<?php echo $item->url; ?>" alt="<?php echo $item->caption; ?>" /><?php if($item->link): ?></a><?php endif; ?>
        <?php if($item->caption): ?>
        <div class="slideshow_item_caption"><p><?php echo $item->caption; ?></p></div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
  </div>
</div>

<script type="text/javascript">
$('#<?php echo $slideshowId; ?>').slides({
    container: 'slideshow_container',
    preload: true,
    preloadImage: '<?php echo $assetsUrl; ?>images/loading.gif',
    play: <?php echo (int) $module->setting('play_speed', 5000); ?>,
    pause: <?php echo (int) $module->setting('pause_speed', 2500); ?>,
    hoverPause: true,
    animationStart: function(current) {
        $('.slideshow_item_caption').stop().animate({
            bottom: -35
        },100);
    },
    animationComplete: function(current) {
        $('.slideshow_item_caption').stop().animate({
            bottom:0
        },200);
    },
    slidesLoaded: function() {
        $('.slideshow_item_caption').stop().animate({
            bottom:0
        },200);
    }
});
</script>

<?php
// Add javsacript for slideshow to page
$asset = $view->helper('Asset');
$view->head()->script($assetsUrl . '/scripts/jquery.slides.min.js');
$view->head()->append('
<style type="text/css">
    #' . $slideshowId . ' .slides_container { position: relative; width: ' . (int) $module->setting('width', 400) . 'px; display: none; }
    #' . $slideshowId . ' .slideshow_item { width: ' . (int) $module->setting('width', 400) . 'px; height: ' . (int) $module->setting('height', 300) . 'px; display: block; }
</style>');
?>