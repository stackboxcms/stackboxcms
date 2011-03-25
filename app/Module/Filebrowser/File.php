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
        return (false !== strpos($this->file->getFileName(), 'image'));
    }


    /**
     * Return file extension
     * 
     * @return string
     */
    public function getExtension()
    {
        // Get and set extension
        $fileNameParts = explode('.', $fileData['name']);
        return end($fileNameParts);
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