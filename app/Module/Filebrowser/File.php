<?php
namespace Module\Filebrowser;

class File
{
    protected $file;


    /**
     * Constructor
     * @return string Format: <hash>.ext
     */
    public function __construct(\SplFileInfo $fileInfo)
    {
        $this->file = $fileInfo;
    }


    /**
     * Is file image?
     * 
     * @return boolean
     */
    public function isImage()
    {
        return in_array($this->getExtension(), array('bmp', 'gif', 'ico', 'iff', 'jb2', 'jp2', 'jpc', 'jpg', 'jpeg', 'jpx', 'gif', 'png', 'psd', 'swf', 'tiff', 'tiff', 'unknown', 'wbmp', 'xbm'));
    }


    /**
     * Return file extension
     * 
     * @return string
     */
    public function getExtension()
    {
        return str_replace('.', '', strrchr($this->file->getFileName(), '.'));
    }


    /**
     * Return the full URL to the image
     * 
     * @return string
     */
    public function getUrl()
    {
        $filesDir = \Kernel()->config('cms.path.files');
        return \Kernel()->config('cms.url.files') . str_replace($filesDir, '', $this->getPathname());
    }


    /**
     * Return the full URL to the image for a specific size
     * 
     * @return string
     */
    public function getSizeUrl($width, $height)
    {
        $kernel = \Kernel();
        $site = $kernel->site();
        $filesDir = $kernel->config('cms.path.files') . 'images/';
        $imageFile = str_replace($filesDir, '', $this->getPathname());
        return $kernel->url(array(
            'site' => $site->shortname(),
            'width' => $width,
            'height' => $height,
            'image' => $imageFile
        ), 'image_size');
    }


    /**
     * Passthru call to file object
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        if(is_callable(array($this->file, $method))) {
            return call_user_func_array(array($this->file, $method), $args);   
        }
        throw new \BadMethodCallException("Method '" . $method . "' does not exist");
    }
}