<?php
/**
 * Base application module
 * Used as a base module class other modules must extend from
 */
abstract class App_Module
{
	protected static $connection;
	protected static $session;
	
	protected $app;
	protected $file = __FILE__;
	
	
	/**
	 * To handle Spot dependency
	 */
	public function __construct(Spot $app)
	{
		$this->app = $app;
	}
	
	
	/**
	 * Called immediately upon instantiation, before the action is called
	 */
	public function init() {}
	
	
	/**
	 * Return current class path, based on given '$file' class var
	 */
	public function path()
	{
		return dirname($this->file);
	}
	
	
	/**
	 * Return current module name, based on class naming conventions
	 * Expected: [Name]_Module
	 */
	public function name()
	{
		return str_replace("_Module", "", get_class($this));
	}
	
	
	/**
	 * Return a request object to work with
	 */
	public function request()
	{
		return $this->app->request();
	}
	
	
	/**
	 * Return a session object to work with
	 */
	public function session()
	{
		if(null === self::$session) {
			self::$session = new App_Session();
		}
		return self::$session;
	}
	
	
	/**
	 * New module view template
	 *
	 * @param string $template Template name/path
	 */
	public function view($template, $format = "html")
	{
		$view = new App_View($template, $format, $this->path() . "/views/");
		$view->format($this->request()->format);
		$view->set('app', $this->app);
		return $view;
	}
	
	
	/**
	 * Get instance of database connection
	 */
	public function connection()
	{
		if(null === self::$connection) {
			$cfg = $this->app->config('database');
			if($this->app->load('phpDataMapper_Database_Adapter_Mysql')) {
				self::$connection = new phpDataMapper_Database_Adapter_Mysql($cfg['host'], $cfg['dbname'], $cfg['username'], $cfg['password']);
			} else {
				throw new Exception("Unable to load database connection");
			}
		}
		return self::$connection;
	}
	
	
	/**
	 * Get mapper object to work with
	 * @todo Ensure only one instance of a mapper gets loaded
	 */
	public function mapper($mapperName = null)
	{
		// Append given name, if any
		if(null === $mapperName) {
			$mapperName = $this->name();
		}
		
		// Append 'Mapper' to the end, as per convention
		$mapperName .=  "_Mapper";
		
		// Ensure file can be loaded
		if(!$this->app->load($mapperName, $this->app->config('path.modules'))) {
			throw new Exception("Unable to load class '" . $mapperName . "' - requested class not found");
		}
		
		// Create new mapper, passing in adapter connection
		$mapper = new $mapperName($this->connection());
		return $mapper;
	}
}