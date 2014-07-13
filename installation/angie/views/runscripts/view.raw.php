<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewRunscripts extends AView
{
    public function onBeforeMain()
    {
        // Load system defines
        if (file_exists(APATH_ROOT . '/defines.php'))
        {
            include_once APATH_ROOT . '/defines.php';
        }

        if (!defined('_JDEFINES'))
        {
            define('JPATH_BASE', APATH_SITE);
            require_once JPATH_BASE . '/includes/defines.php';
        }

        // Load the rest of the framework include files
        if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
        {
            require_once JPATH_LIBRARIES . '/import.legacy.php';
        }
        else
        {
            require_once JPATH_LIBRARIES . '/import.php';
        }
        require_once JPATH_LIBRARIES . '/cms.php';

        // You can't fix stupid… but you can try working around it
        if( (!function_exists('json_encode')) || (!function_exists('json_decode')) )
        {
            require_once JPATH_ADMINISTRATOR . '/components/com_akeeba/helpers/jsonlib.php';
        }

        // Load the JApplicationCli class
        JLoader::import('joomla.application.web');

        require_once APATH_INSTALLATION.'/angie/assets/runscripts.php';

        $run = JApplicationWeb::getInstance('RunScripts');

        $run->execute();

        return false;
    }
}