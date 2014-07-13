<?php

class WrightAdapterJoomlaNav
{
	public function render($args)
	{
		// Set module name
		if (!isset($args['name'])) $args['name'] = 'menu';
		// Set module name
		if (!isset($args['style'])) $args['style'] = 'raw';
		// Set module name
		if (!isset($args['class'])) $args['class'] = 'container';
		// Set module name
		if (!isset($args['wrapclass'])) $args['wrapclass'] = '';
		if (!isset($args['wrapper'])) $args['wrapper'] = 'wrapper-' . $args['name'];

		$wrapper = "";
		switch ($args['type'])
		{
		    case 'row-fluid' :
				$wrapper = '<div class="'.$args['type'].'">';
		        break;
			default :
				$wrapper = '<div class="'.$args['wrapper'].'">';
				break;
		}


		$nav = $wrapper . '
			<nav id="'.$args['name'].'">
				<div class="navbar ' . $args['wrapclass'] . '">
					<div class="navbar-inner">
						<div class="' . $args['class'] . '">
				            <a class="btn btn-navbar" data-toggle="collapse" data-target="#nav-'.$args['name'].'">
					            <span class="icon-bar"></span>
					            <span class="icon-bar"></span>
					            <span class="icon-bar"></span>
				            </a>
				            <div class="nav-collapse" id="nav-'.$args['name'].'">
								 <jdoc:include type="modules" name="'.$args['name'].'" style="'.$args['style'].'" />
							</div>
						</div>
					</div>
				</div>
			</nav>
		</div>';
		return $nav;
	}
}
