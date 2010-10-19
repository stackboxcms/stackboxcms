
<div class="module_blog">
<?php if(count($this->posts) > 0): ?>
  <?php foreach($this->posts as $post): ?>
    <div class="module_blog_post">
      <h2><?php echo $post->title; ?></h2>
      <p><small><?php echo date('F j Y', strtotime($post->date_published)); ?></small></p>
      <p><?php echo $post->description; ?></p>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <p>No blog posts to show</p>
  
  <?php $this->cache(function($view) { ?>
    <?php
    // Expensive external HTTP request
    echo "Cached content";
    ?>
  <?php }, 'github_zf2_commits', 3600); ?>
  
<?php endif; ?>
</div>