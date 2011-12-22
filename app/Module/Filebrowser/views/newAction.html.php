<?php 
$form = $view->generic('form')
    ->type('upload')
    ->action($view->url(array('action' => 'post'), 'filebrowser', \Kernel()->request()->query()))
    ->method('post')
    ->fields(array(
        'upload' => array('type' => 'file')
    ))
    ->submit('Upload');
echo $form->content();
?>