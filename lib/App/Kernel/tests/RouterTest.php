<?php
// Require Spot test
require_once 'KernelTest.php';
 
/**
 * Spot generic tests
 */
class RouterTest extends KernelTest
{
	// Test Spot instance
	public function setUp()
	{
		parent::setUp();
		
		$this->router = $this->kernel->router();
		if($this->router instanceof AppKernel_Router) {
			$this->router->reset();
		}
	}
	
	public function testInstance()
	{
		$this->assertTrue($this->router instanceof AppKernel_Router);
	}
	
	public function testReset()
	{
		$this->assertEquals(0, count($this->router->routes()));
		
		$this->router->route('(:test)');
		$this->assertEquals(1, count($this->router->routes()));
		
		$this->router->reset();
		$this->assertEquals(0, count($this->router->routes()));
	}
	
	public function testRouteSingleAlpha()
	{		
		$this->router->route('(:module)');
		$params = $this->router->match("test");
		$this->assertEquals('test', $params['module']);
		
		// With beginning slash
		$params = $this->router->match("/test");
		$this->assertEquals('test', $params['module']);
	}
	
	public function testRouteMVCAction()
	{
		$router = $this->router;	
		$router->route('(:controller)/(:action).(:format)');
		
		$params = $router->match("/user/profile.html");
		
		$this->assertEquals('user', $params['controller']);
		$this->assertEquals('profile', $params['action']);
		$this->assertEquals('html', $params['format']);
	}
	
	public function testRouteMVCItem()
	{
		$router = $this->router;	
		$router->route('(:controller)/(:action)/(#id).(:format)');
		
		$params = $router->match("/blog/show/55.json");
		
		$this->assertEquals('blog', $params['controller']);
		$this->assertEquals('show', $params['action']);
		$this->assertEquals('55', $params['id']);
		$this->assertEquals('json', $params['format']);
	}
	
	public function testRouteBlogPost()
	{
		$router = $this->router;	
		$router->route('(:dir)/(#year)/(#month)/(:slug)');
		
		$params = $router->match("/blog/2009/10/blog-post-title");
		
		$this->assertEquals('blog', $params['dir']);
		$this->assertEquals('2009', $params['year']);
		$this->assertEquals('10', $params['month']);
		$this->assertEquals('blog-post-title', $params['slug']);
	}
	
	public function testRouteWildcard()
	{
		$router = $this->router;
			
		$router->route('(*url)');
		$params = $router->match("/blog/2009/10/27/my-post-title");
		$this->assertEquals('blog/2009/10/27/my-post-title', $params['url']);
	}
	
	public function testRouteWildcard2()
	{
		$router = $this->router;
			
		$router->route('(:dir)/(*url)');
		$params = $router->match("/blog/2009/10/27/my-post-title");
		$this->assertEquals('blog', $params['dir']);
		$this->assertEquals('2009/10/27/my-post-title', $params['url']);
	}
}