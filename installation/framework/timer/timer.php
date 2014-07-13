<?php
/**
 * @package angifw
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

class ATimer
{
	/** 
	 * Maximum execution time allowance per step
	 * 
	 * @var  int
	 */
	private $max_exec_time = null;

	/** 
	 * Timestamp of execution start
	 * 
	 * @var  int 
	 */
	private $start_time = null;

	/**
	 * Public constructor, creates the timer object and calculates the execution
	 * time limits.
	 * 
	 * @return  ATimer
	 */
	public function __construct($max_exec_time = 5, $runtime_bias = 75)
	{
		ALog::_(ANGIE_LOG_DEBUG, __METHOD__ . '(' . $max_exec_time . ', ' . $runtime_bias . ')');
		
		// Initialize start time
		$this->start_time = $this->microtime_float();
		
		$this->max_exec_time = $max_exec_time * $runtime_bias / 100;
	}

	/**
	 * Wake-up function to reset internal timer when we get unserialized
	 */
	public function __wakeup()
	{
		// Re-initialize start time on wake-up
		$this->start_time = $this->microtime_float();
	}

	/**
	 * Gets the number of seconds left, before we hit the "must break" threshold
	 * 
	 * @return  float
	 */
	public function getTimeLeft()
	{
		return $this->max_exec_time - $this->getRunningTime();
	}

	/**
	 * Gets the time elapsed since object creation/unserialization, effectively
	 * how long this step is running
	 * 
	 * @return  float
	 */
	public function getRunningTime()
	{
		return $this->microtime_float() - $this->start_time;
	}

	/**
	 * Returns the current timestamp in decimal seconds
	 * 
	 * @return  float
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * Reset the timer
	 */
	public function resetTime()
	{
		$this->start_time = $this->microtime_float();
	}

}