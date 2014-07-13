<?php

class WrightAdapterJoomlaLogo
{
	// checks the existance of a logo
	public static function isThereALogo() {
		$dochtml = JFactory::getDocument();

		// if is set as a module position 'logo', checks if there is any module in that position
		if ($dochtml->params->get('logo', 'template') == 'module') {
			if ($dochtml->countModules('logo'))
				return true;
			return false;
		}

		// in any other case, there is always a logo (at least as Wright's default image logo.png)
		return true;
	}

	public function render($args)
	{
		if (!isset($args['name'])) $args['name'] = 'newsflash';
		if (!isset($args['style'])) $args['style'] = 'xhtml';

		$doc = Wright::getInstance();
		$app = JFactory::getApplication();

		$title = '<h2>'.(($doc->document->params->get('headline', '') !== '') ? $doc->document->params->get('headline') : $app->getCfg('sitename')).'</h2>';
		$title .= ($doc->document->params->get('tagline', '') !== '') ? '<h3>'.$doc->document->params->get('tagline').'</h3>' : '';

		// If user wants a module, load it instead of image
		if ($doc->document->params->get('logo', 'template') == 'module') {
			$html = '<div id="logo" class="span'.$doc->document->params->get('logowidth', '6').'"><jdoc:include type="modules" name="logo" /></div>';
			if ($doc->document->params->get('logowidth') !== '12') $html .= '<div id="'.$args['name'].'" class="span'.(12 - $doc->document->params->get('logowidth', '6')).'"><jdoc:include type="modules" name="'.$args['name'].'" style="'.$args['style'].'" /></div>';
			//$html .= '<div class="clearfix"></div>';
			return $html;
		}

		// If user wants just a title, print it out
		elseif ($doc->document->params->get('logo', 'template') == 'title') {
			$html = '<div id="logo" class="span'.$doc->document->params->get('logowidth', '6').'"><a href="'.JURI::root().'" class="title">'.$title.'</a></div>';
			if ($doc->document->params->get('logowidth') !== '12') $html .= '<div id="'.$args['name'].'" class="span'.(12 - $doc->document->params->get('logowidth', '6')).'"><jdoc:include type="modules" name="'.$args['name'].'" style="'.$args['style'].'" /></div>';
			//$html .= '<div class="clearfix"></div>';
			return $html;
		}

		// If user wants an image, decide which image to load
		elseif ($doc->document->params->get('logo', 'template') == 'template') {
			// added support for logo tone (light = "", dark = "-Dark")
			$user = JFactory::getUser();
			$logotone = ($user->getParam('templateTone',''));
			if ($logotone == '') {
				$logotone =  $doc->document->params->get('Tone','' );
			}

			if (is_file(JPATH_ROOT.'/'.'templates'.'/'.$doc->document->template.'/'.'images'.'/'.$doc->document->params->get('style').'/'.'logo' . $logotone. '.png'))
				$logo = JURI::root().'templates/'.$doc->document->template.'/images/'.$doc->document->params->get('style').'/logo' . $logotone. '.png';
			elseif (is_file(JPATH_ROOT.'/'.'templates'.'/'.$doc->document->template.'/'.'images'.'/'.$doc->document->params->get('style').'/'.'logo.png'))
				$logo = JURI::root().'templates/'.$doc->document->template.'/images/'.$doc->document->params->get('style').'/logo.png';
			elseif (is_file(JPATH_ROOT.'/'.'templates/'.$doc->document->template.'/images/logo' . $logotone. '.png'))
				$logo = JURI::root().'templates/'.$doc->document->template.'/images/logo' . $logotone. '.png';
			elseif (is_file(JPATH_ROOT.'/'.'templates/'.$doc->document->template.'/images/logo.png'))
				$logo = JURI::root().'templates/'.$doc->document->template.'/images/logo.png';
			else {
				$logo = JURI::root().'templates/'.$doc->document->template.'/wright/images/logo.png';
			}
		}
		else {
			$logo = JURI::root().'images/'.$doc->document->params->get('logo', 'logo.png');
		}

		$html = '<div id="logo" class="span'.$doc->document->params->get('logowidth', '6').'"><a href="'.JURI::root().'" class="image">'.$title.'<img src="'.$logo.'" alt="" title="" /></a></div>';
		if ($doc->document->params->get('logowidth') !== '12' && $doc->countModules($args['name'])) {
			if ($args['name'] == 'menu') {
				$html .= '
					<nav id="'.$args['name'].'" class="span'.(12 - $doc->document->params->get('logowidth', '6')).'">
						<div class="navbar">
							<div class="navbar-inner">
								<div class="container">
						            <a class="btn btn-navbar" data-toggle="collapse" data-target="#nav-'.$args['name'].'">
							            <span class="icon-bar"></span>
							            <span class="icon-bar"></span>
							            <span class="icon-bar"></span>
						            </a>
						            <div class="nav-collapse" id="nav-'.$args['name'].'">
										 <jdoc:include type="modules" name="'.$args['name'].'" style="raw" />
									</div>
								</div>
							</div>
						</div>
					</nav>
				';
			}
			else {
				$html .= '<div id="'.$args['name'].'" class="span'.(12 - $doc->document->params->get('logowidth', '6')).'"><jdoc:include type="modules" name="'.$args['name'].'" style="'.$args['style'].'" /></div>';
			}
		} 
		return $html;
	}
}
