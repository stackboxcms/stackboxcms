<?php
namespace Module\Filebrowser;
use Stackbox;

/**
 * Filebrowser Controller
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction($request)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();
        $user = $kernel->user();
        
        /*
        // Ensure page exists
        $mapper = $kernel->mapper();
        $pageMapper = $kernel->mapper('Module\Page\Mapper');
        $pageUrl = Entity::formatPageUrl($pageUrl);
        $page = $pageMapper->getPageByUrl($pageUrl);
        */

        // return
    }
    
    
    /**
     * Form to create a new page
     * @method GET
     */
    public function newAction($request)
    {
        return $this->formView()
            ->method('post')
            ->action($this->kernel->url(array('page' => '/'), 'page'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request)
    {
        $kernel = $this->kernel;
        
        // Ensure page exists
        $mapper = $this->kernel->mapper('Module\Page\Mapper');
        $page = $mapper->getPageByUrl($request->page);
        if(!$page) {
            throw new \Alloy\Exception_FileNotFound("Page not found: '" . $request->page . "'");
        }
        
        return $this->formView()
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
    public function deleteAction($request)
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
    public function deleteMethod($request)
    {
        
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        // Ensure proper directory exists and is writable
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        
    }
}