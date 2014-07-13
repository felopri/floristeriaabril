<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class RunScripts extends JApplicationWeb
{
    public function doExecute()
    {
        // The script file requires an installer instance, however it's not used inside the code...
        $installer  = JInstaller::getInstance();
        $scriptFile = JPATH_ROOT.'/administrator/components/com_admin/script.php';

        if (!is_file($scriptFile))
        {
            return;
        }

        include_once $scriptFile;

        $classname = 'JoomlaInstallerScript';

        if (!class_exists($classname))
        {
            return;
        }

        $manifestClass = new $classname();

        if ($manifestClass && method_exists($manifestClass, 'update'))
        {
            $manifestClass->update($installer);
        }
    }
}