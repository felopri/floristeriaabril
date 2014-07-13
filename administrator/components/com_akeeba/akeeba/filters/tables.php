<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 */

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Database table exclusion filter
 */
class AEFilterTables extends AEAbstractFilter
{
	function __construct()
	{
		$this->object = 'dbobject';
		$this->subtype = 'all';
		$this->method = 'direct';

		if (AEFactory::getKettenrad()->getTag() == 'restorepoint')
		{
			$this->enabled = false;
		}

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}

		if (AEFactory::getKettenrad()->getTag() == 'restorepoint')
		{
			$this->enabled = false;
		}

		parent::__construct();
	}
}