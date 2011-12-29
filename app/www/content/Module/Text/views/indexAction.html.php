<?php
// Note
if('note' == $item->type):
?>
  <div class="alert-message block-message info"><?php echo $item->content; ?></div>
<?php
// Warning
elseif('warning' == $item->type):
?>
  <div class="alert-message block-message warning"><?php echo $item->content; ?></div>
<?php
// Code
elseif('code' == $item->type):
?>
  <div class="module_text_code">
    <pre>
    <?php echo htmlentities($item->content, ENT_QUOTES, 'UTF-8'); ?>
    </pre>
  </div>
<?php
// Default
else:
  echo $item->content;
endif;
?>
