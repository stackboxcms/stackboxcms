<?php
$item = $this->item;

// Note
if('note' == $item->type):
?>
    <div class="module_text_note"><?php echo $item->content; ?></div>
<?php
// Warning
elseif('warning' == $item->type):
?>
    <div class="module_text_warning"><?php echo $item->content; ?></div>
<?php
// Code
elseif('code' == $item->type):
?>
    <code class="module_text_code"><pre>
    <?php echo htmlentities($item->content, ENT_QUOTES, 'UTF-8'); ?>
    </pre></code>
<?php
// Default
else:
    echo $item->content;
endif;
?>
