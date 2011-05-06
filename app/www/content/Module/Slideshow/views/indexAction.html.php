<?php
$slideshowId = 'slideshow_' . $module->id;
$assetsUrl = $kernel->config('url.root') . $kernel->config('cms.dir.modules') . 'Module/' . $module->name . '/assets/';
?>

<div id="<?php echo $slideshowId; ?>">
  <div class="slides_container">
<?php foreach($items as $item): ?>
    <div class="slideshow_item">
        <img src="<?php echo $item->url; ?>" alt="<?php echo $item->caption; ?>" />
        <div class="slideshow_item_caption"><p><?php echo $item->caption; ?></p></div>
    </div>
<?php endforeach; ?>
  </div>
</div>

<script type="text/javascript">
$('#<?php echo $slideshowId; ?>').slides({
    preload: true,
    preloadImage: '<?php echo $assetsUrl; ?>images/loading.gif',
    play: 5000,
    pause: 2500,
    hoverPause: true,
    animationStart: function(current) {
        $('.slideshow_item_caption').animate({
            bottom: -35
        },100);
        if (window.console && console.log) {
            // example return of current slide number
            console.log('animationStart on slide: ', current);
        };
    },
    animationComplete: function(current) {
        $('.slideshow_item_caption').animate({
            bottom:0
        },200);
        if (window.console && console.log) {
            // example return of current slide number
            console.log('animationComplete on slide: ', current);
        };
    },
    slidesLoaded: function() {
        $('.slideshow_item_caption').animate({
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
    #' . $slideshowId . ' .slides_container { width: 470px; display: none; }
    #' . $slideshowId . ' .slideshow_item { width: 470px; height: 400px; display: block; }
</style>');
?>