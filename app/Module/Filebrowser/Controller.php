<?php
namespace Module\Filebrowser;
use Alloy, Stackbox;
use Imagine, Imagine\Image;

/**
 * Filebrowser Controller
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    // So template helper knows where to find views... (not in standard modules directory at www/content)
    protected $_path = __DIR__;


    /**
     * Ensure user is logged-in to see ANY of this module
     */
    public function init($action = null)
    {
        // Always allow images to be resized (even for visitors - no auth)
        if('imageSizeAction' == $action || 'imageSize' == $action) {
            return;
        }

        $user = \Kernel()->user();
        if(!$user || !$user->isLoggedIn()) {
            throw new Alloy\Exception\Auth("User must be logged in to view files");
        }
    }


    /**
     * @method GET
     */
    public function indexAction(Alloy\Request $request)
    {
        return false;
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
        $dir = \Kernel()->config('cms.path.files') . 'images/';
        $this->ensureDirectoryAvailable($dir);
        
        return $this->template('directoryList')
            ->set(array('directory' => $dir))
            ->layout('ajax');
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
        $dir = \Kernel()->config('cms.path.files') . 'files/';
        $this->ensureDirectoryAvailable($dir);
        
        return $this->template('directoryList')
            ->set(array('directory' => $dir))
            ->layout('ajax');
    }


    /**
     * Resize and save image on the fly
     * @method GET
     */
    public function imageSizeAction(Alloy\Request $request)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();
        $user = $kernel->user();

        // Ensure proper directories exist and are writable
        $dir = \Kernel()->config('cms.path.files') . 'images';
        $this->ensureDirectoryAvailable($dir);
        
        // Sort out request data
        $image = $request->image;
        $width = (int) $request->width;
        $height = (int) $request->height;
        $imagePath = realpath(dirname($dir . '/' . $image));

        // Ensure directory is beginning part of fully expanded path (security issues with '../' in path)
        if(0 !== strpos($imagePath, $dir)) {
            return false;
        }

        // Ensure image file exists
        if(!file_exists($imagePath . '/' . $image)) {
            return false;
        }

        // Ensure resize directory exists and is writable
        $resizeDir = $dir . '/_size/' . $width . 'x' . $height . '/';
        $this->ensureDirectoryAvailable(dirname($resizeDir . $image));

        // Resize to requested width/height
        $image = $kernel->imagine()
            ->open($imagePath . '/' . $image)
            ->thumbnail(new Imagine\Image\Box($width, $height), Imagine\ImageInterface::THUMBNAIL_INSET)
            ->save($resizeDir . $image);

        // Send image content to browser
        header('Content-type: image/png');
        echo $image->get('png');
        exit();
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
        $uploadDir = $kernel->config('cms.path.files');
        
        // @todo Support multiple file uploads
        $subDir = 'files';
        if(isset($_FILES['upload'])) {
            $fileData = $_FILES['upload'];
            $fileName = $kernel->formatUrl($fileData['name']);
            $fileName = substr($fileName, 0, strrpos($fileName, '-')) . strrchr($fileData['name'], '.');

            // May want to take into account all the file upload errors...
            // @link http://us3.php.net/manual/en/features.file-upload.errors.php
            if($fileData['error'] == UPLOAD_ERR_OK) {
                // See if file is image or not
                if(false !== strpos($fileData['type'], 'image')) {
                    $subDir = 'images';
                }

                // Attempt to move file to new location
                $uploadDir .= $subDir; // 'images' or 'files'

                // Save file to new location
                $this->ensureDirectoryAvailable($uploadDir);
                if(move_uploaded_file($fileData['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $saveResult = true;
                }
            }
        }
        // ===========================================================================

        if($saveResult) {
            // CKEditor custom response
            // @see http://docs.cksource.com/CKEditor_3.x/Developers_Guide/File_Browser_(Uploader)/Custom_File_Browser
            if($request->get('CKEditor')) {
                $callback = $request->get('CKEditorFuncNum');
                $url = $kernel->config('cms.url.files') . $subDir . '/' . $fileName;
                $err = '';

                // CKEditor relies on receiving this custom callback after successful upload
                return '
                <script type="text/javascript">
                  try {
                    window.parent.CKEDITOR.tools.callFunction(' . $callback . ', "' . $url . '", "' . $err . '");
                  } catch(e) {}
                </script>
                ';
            }

            // Redirect to images or files
            if($subDir = 'images') {
                return $kernel->redirect($kernel->url(array('action' => 'images'), 'filebrowser'));
            }
            return $kernel->redirect($kernel->url(array('action' => 'files'), 'filebrowser'));
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