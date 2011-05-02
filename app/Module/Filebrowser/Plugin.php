<?php
namespace Module\Filebrowser;
use Alloy;

/**
 * Filebrowser plugin
 * Adds filebrowser link methods to the kernel for filebrowser popup file/image selection windows
 */
class Plugin
{
    protected $kernel;


    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;

        // Link to FileBrowser module to get an image link
        $kernel->addMethod('filebrowserSelectImageLink', function($fieldId = null) use($kernel) {
           return '<a href="' . $kernel->url(array('action' => 'images'), 'filebrowser', array('field_fill_id' => $fieldId)) . '" class="filebrowser_selection filebrowser_select_image" rel="popup">Select Image...</a>';
        });

        // Link to FileBrowser module to get an image link
        $kernel->addMethod('filebrowserSelectFileLink', function($fieldId = null) use($kernel) {
           return '<a href="' . $kernel->url(array('action' => 'files'), 'filebrowser', array('field_fill_id' => $fieldId)) . '" class="filebrowser_selection filebrowser_select_file" rel="popup">Select File...</a>';
        });
    }
}