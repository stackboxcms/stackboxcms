<ul>
<?php foreach($items as $item): ?>
  <li><img src="<?php echo $item->url; ?>" alt="<?php echo $item->caption; ?>" /></li>
<?php endforeach; ?>
</ul>