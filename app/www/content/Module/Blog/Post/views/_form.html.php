<div class="module_blog">
    <p>
        <a href="<?php echo $kernel->url(array('page' => $page->url, 'module_name' => 'blog', 'module_id' => $module->id, 'module_action' => 'editlist'), 'module'); ?>" class="cms_button_back">Back</a>
    </p>

    <?php echo $form->content(); ?>
</div>