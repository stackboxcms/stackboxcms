<?php
namespace Module\Filebrowser;
use Alloy, Stackbox;

/**
 * Filebrowser Controller
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    // So template helper knows where to find views... (not in standard modules directory at www/content)
    protected $_path = __DIR__;


    /**
     * @method GET
     */
    public function indexAction(Alloy\Request $request)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();
        $user = $kernel->user();

        // Ensure proper directories exist and are writable
        $uploadDir = \Kernel()->config('cms.path.files');
        $imagesDir = $uploadDir . 'images/';
        $filesDir = $uploadDir . 'files/';

        $this->ensureDirectoryAvailable($filesDir);
        $this->ensureDirectoryAvailable($imagesDir);
        
        // Template
        return $this->template(__FUNCTION__);
    }


    /**
     * List all images
     * @method GET
     */
    public function imagesAction(Alloy\Request $request)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();
        $user = $kernel->user();

        // Ensure proper directories exist and are writable
        $uploadDir = \Kernel()->config('cms.path.files');
        $imagesDir = $uploadDir . 'images/';
        $this->ensureDirectoryAvailable($imagesDir);
        
        return $this->template('directoryList')
            ->set(array('directory' => $imagesDir));
    }


    /**
     * List all other files
     * @method GET
     */
    public function filesAction(Alloy\Request $request)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();
        $user = $kernel->user();
        
        // Ensure proper directories exist and are writable
        $uploadDir = \Kernel()->config('cms.path.files');
        $imagesDir = $uploadDir . 'images/';
        $this->ensureDirectoryAvailable($imagesDir);
        
        return $this->template('directoryList')
            ->set(array('directory' => $imagesDir));
    }
    
    
    /**
     * Form to create a new page
     * @method GET
     */
    public function newAction(Alloy\Request $request)
    {
        return $this->template(__FUNCTION__);
    }
    
    
    /**
     * @method GET
     */
    public function editAction(Alloy\Request $request)
    {
        $kernel = $this->kernel;
        
        // Ensure page exists
        $mapper = $this->kernel->mapper('Module\Page\Mapper');
        $page = $mapper->getPageByUrl($request->page);
        if(!$page) {
            throw new \Alloy\Exception_FileNotFound("Page not found: '" . $request->page . "'");
        }
        
        return $this->newAction($request)
            ->data($page->data());
    }
    
    
    /**
     * New file upload
     * @method POST
     */
    public function postMethod(Alloy\Request $request)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();
        $user = $kernel->user();

        // Upload File
        // ===========================================================================
        $saveResult = false;

        // Project file path (full root path)
        $uploadDir = '';
        
        var_dump($_FILES);

        // Loop over each uploaded file
        $fileData = $_FILES['upload'];
        $fileName = $fileData['name'];

        // May want to take into account all the file upload errors...
        // @link http://us3.php.net/manual/en/features.file-upload.errors.php
        if($fileData['error'] == UPLOAD_ERR_OK) {
            // Attempt to move file to new location
            if(move_uploaded_file($fileData['tmp_name'], $uploadDir . '/' . $fileName)) {
                $saveResult = true;
            }
        }
        // ===========================================================================

        if($saveResult) {
            // @todo Set name of file
            return $kernel->resource()
                ->status(201);
        } else {
            return $kernel->resource()
                ->status(400)
                ->errors(array(
                    'file' => array('Unable to upload file')
                    ));
        }
    }
    
    
    /**
     * Display delete confirmation
     * @method GET
     */
    public function deleteAction(Alloy\Request $request)
    {
        if($request->format == 'html') {
            $view = new \Alloy\View\Generic\Form('form');
            $form = $view
                ->method('delete')
                ->action($this->kernel->url(array('page' => $request->page), 'page'))
                ->submitButtonText('Delete');
            return "<p>Are you sure you want to delete this file?</p>" . $form;
        }
        return false;
    }
    
    
    /**
     * Delete file
     * @method DELETE
     */
    public function deleteMethod(Alloy\Request $request)
    {
        
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        // Ensure proper directories exist and are writable
        $uploadDir = \Kernel()->config('cms.path.uploads');
        $imagesDir = $uploadDir . 'images/';
        $filesDir = $uploadDir . 'files/';

        $this->ensureDirectoryAvailable($imagesDir);
        $this->ensureDirectoryAvailable($filesDir);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        
    }


    /**
     * Kind of helper function to ensure the proper directories exist and are writable.
     * 
     * Private because this particular implementation may be changed soon.
     *   Not sure I like a helper function on a controller...
     * 
     * @throws \Exception
     */
    private function ensureDirectoryAvailable($dir, $chmod = 0755)
    {
        // Directory exists?
        $dirAvailable = false;
        if(is_dir($dir)) {
            $dirAvailable = true;
        } else {
            $dirAvailable = mkdir($dir, $chmod, true);
        }

        // Directory is writeable?
        if($dirAvailable && !is_writable($dir)) {
            $dirAvailable = chmod($dir, $chmod);
        }

        // Exception if not available
        if(!$dirAvailable) {
            throw new \Exception("Unable to ensure directory exists and are writable.\n
                Directory: " . $dir . "\n
            ");
        }

        return $dirAvailable;
    }
}