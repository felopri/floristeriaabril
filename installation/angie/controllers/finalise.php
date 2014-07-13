<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerFinalise extends AController
{
	public function cleanup()
	{
		try
		{
			$result = $this->getThisModel()->cleanup();
		}
		catch (Exception $exc)
		{
			$result = false;
		}

		// If we have removed files, ANGIE will return a 500 Internal Server
		// Error instead of the result. This works around it.
		@ob_end_clean();
		echo '###'.json_encode($result).'###';
		die();
	}
}