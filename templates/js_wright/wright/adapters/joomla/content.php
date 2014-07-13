<?php

class WrightAdapterJoomlaContent
{
	public function render($args)
	{
		$content = '<jdoc:include type="message" />';

		$content .= '<jdoc:include type="component" />';
		
		return $content;
	}
}
