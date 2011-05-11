
<div class="module_blog">
<?php if(isset($posts) && count($posts) > 0): ?>
  <?php foreach($posts as $post): ?>
    <div class="module_blog_post">
      <h2><?php echo $view->link($post->title, array('page' => $page->url, 'module_name' => 'Blog_Post', 'module_id' => $module->id, 'module_item' => $post->id), 'module_item'); ?></h2>
      <p><small><?php echo $view->toDate($post->date_published); ?></small></p>
      <p><?php echo $post->description; ?></p>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <p>No blog posts to show</p>
  
  <?php $view->cache(function($view) { ?>
    <?php
    // Expensive external HTTP request
    echo "Cached content";
    ?>
  <?php }, 3600, 'github_zf2_commits'); ?>
  
<?php endif; ?>
</div>