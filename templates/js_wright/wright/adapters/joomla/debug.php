<?php

class WrightAdapterJoomlaDebug
{
	public function render($args)
	{
		return '<jdoc:include type="modules" name="debug" />';
	}
}