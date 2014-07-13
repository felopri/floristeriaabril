<?php

class Overrider
{
	static $version;

	public static function getVersion()
	{
		if (!isset(self::$version)) {
			jimport('joomla.version');
			$version = new JVersion();
			self::$version = explode('.', $version->RELEASE);
		}

		return self::$version;
	}

	public static function getOverride($extension, $layout = 'default')
	{
		$type = substr($extension, 0, 3);

		$file = '';

		$app = JFactory::getApplication();

        $version = self::getVersion();

		switch ($type)
		{
			case 'mod' :
                while (!is_file(JPATH_THEMES.'/'.$app->getTemplate().'/'.'wright'.'/'.'html'.'/'.'joomla_'.implode('.', $version).'/'.$extension.'/'.$layout.'.php'))
                {
                    // If running down the list, we need to jump down a major number version
                    // then make sure we don't drop below minimum support
                    // lastly just decrement the minor number
                    if ($version[1] == 0) {
                        $version[0]--;
                        $version[1] = 9;
                    } elseif ($version[0] == 1 && $version[1] == 5) {
                        continue;
                    }
                    else {
                        $version[1]--;
                    }
                }
				$file = JPATH_THEMES.'/'.$app->getTemplate().'/'.'wright'.'/'.'html'.'/'.'joomla_'.implode('.', $version).'/'.$extension.'/'.$layout.'.php';
				break;

			case 'com' :
				list($folder, $view) = explode('.', $extension);
                while (!is_file(JPATH_THEMES.'/'.$app->getTemplate().'/'.'wright'.'/'.'html'.'/'.'joomla_'.implode('.', $version).'/'.$folder.'/'.$view.'/'.$layout.'.php'))
                {
                    // If running down the list, we need to jump down a major number version
                    // then make sure we don't drop below minimum support
                    // lastly just decrement the minor number
                    if ($version[1] == 0) {
                        $version[0]--;
                        $version[1] = 9;
                    } elseif ($version[0] == 1 && $version[1] == 5) {
                        continue;
                    }
                    else {
                        $version[1]--;
                    }
                }
                $file = JPATH_THEMES.'/'.$app->getTemplate().'/'.'wright'.'/'.'html'.'/'.'joomla_'.implode('.', $version).'/'.$folder.'/'.$view.'/'.$layout.'.php';
				break;
		}
		return $file;
	}
}
