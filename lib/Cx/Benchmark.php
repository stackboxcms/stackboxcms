<?php
/*
 * Benchmark class
 * $Id$
 *
 * For testing and measuring code execution times and memory usage
 * 
 * @package Cont-xt Framework
 * @link http://www.cont-xt.com/
 */
class Cx_Benchmark
{
	protected $timeStart = array();
	protected $timeEnd = array();
	
	protected $memoryStart = array();
	protected $memoryEnd = array();
	
	protected $log = array();
	
	// Constructor method
	public function __construct()
	{
		$this->timeStart['__construct'] = $this->getMicrotime();
	}
	
	// Start named timer
	public function startTimer($name = 'default')
	{
		$this->timeStart[$name] = $this->getMicrotime();
	}
	
	// Show current time
	public function getTimer($name = 'default')
	{
		$this->timeEnd[$name] = $this->getMicrotime();
		return number_format($this->timeEnd[$name] - $this->timeStart[$name], 4);
	}
	
	// Start named memory usage meter
	public function startMemory($name = 'default')
	{
		$this->memoryStart[$name] = memory_get_usage();
	}
	
	// Show current memory usage for named marker
	public function getMemory($name = 'default')
	{
		$this->memoryEnd[$name] = memory_get_usage();
		return $this->memoryEnd[$name] - $this->memoryStart[$name];
	}
	
	// Get microtime
	protected function getMicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}