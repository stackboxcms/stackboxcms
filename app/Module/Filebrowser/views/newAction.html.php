<?php 
$form = $view->generic('form')
    ->type('upload')
    ->action($view->url(array('action' => 'new'), 'filebrowser'))
    ->method('post')
    ->fields(array(
        'upload' => array('type' => 'file')
    ))
    ->submit('Upload');
echo $form->content();
?>