<?php
namespace Module\Filebrowser;

class File
{
    protected $file;


    /**
     * Constructor
     * @return string Format: <hash>.ext
     */
    public function __construct($fileInfo)
    {
        $this->file = $fileInfo;

        $this->isDir($fileInfo->isDir());
    }


    /**
     * Is file image?
     * @return boolean
     */
    public function isImage()
    {
        return (false !== strpos($this->file->getFileName(), 'image'));
    }


    /**
     * Return file extension
     */
    public function getExtension()
    {
        // Get and set extension
        $fileNameParts = explode('.', $fileData['name']);
        return end($fileNameParts);
    }


    /**
     * Passthru call to file object
     */
    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->file, $method), $args);
    }
}