<p>Text Module Template Blah</p>
<?php
$view = new Cx_View_Generic_Form($cx);
$view->fields($mapper->fields());
echo $view;
?>