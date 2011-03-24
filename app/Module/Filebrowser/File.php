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
}