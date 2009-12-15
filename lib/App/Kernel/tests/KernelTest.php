<?php
require_once 'PHPUnit/Framework.php';

// Require Spot library
require_once dirname(dirname(dirname(__FILE__))) . '/AppKernel.php';


// Test callback function
function barkSample($word)
{
	return ($word) ? $word : 'Woof!';
}

// Test callback class
class callSample
{
	public function bark($word)
	{
		return ($word) ? $word : 'Woof!';
	}
}

 
/**
 * Spot generic tests
 */
class KernelTest extends PHPUnit_Framework_TestCase
{
	// Test Spot instance
	public function setUp()
	{
		$kernel = AppKernel();
		$this->kernel = $kernel;
	}
	
	// Test Spot instance
	public function testInstance()
	{
		$this->assertTrue($this->spot instanceof Spot);
	}
	
	public function testConfig()
	{
		$cfg = array(
			'debug' => true,
			'foo' => array('bar' => 'baz')
			);
		$this->kernel->config($cfg);
		
		$this->assertTrue($this->kernel->config('debug'));
		$this->assertEquals('baz', $this->kernel->config('foo.bar'));
	}
	
	public function testConfigUpdate()
	{
		// Set initial config
		$cfg = array(
			'debug' => true,
			'foo' => array('bar' => 'baz')
			);
		$this->kernel->config($cfg);
		
		// Update with new config parts
		$cfg = array(
			'debug' => false,
			'foo' => array('bar' => 'baz too')
			);
		$this->kernel->config($cfg);
		
		$this->assertFalse($this->kernel->config('debug'));
		$this->assertEquals('baz too', $this->kernel->config('foo.bar'));
	}
	
	public function testTeachFunction()
	{
		$this->kernel->addMethod('bark', 'barkSample');
		$result = $this->kernel->bark('Arf!');
		$this->assertEquals('Arf!', $result);
	}
	
	public function testTeachClassMethod()
	{
		$callSample = new callSample();
		$this->kernel->addMethod('bark2', array($callSample, 'bark'));
		$result = $this->kernel->bark2('Arf2!');
		$this->assertEquals('Arf2!', $result);
	}
}