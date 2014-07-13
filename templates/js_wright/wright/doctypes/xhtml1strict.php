<?php

require_once('default.php');

class HtmlAdapterXhtml1Strict extends HtmlAdapterAbstract
{
	public function getDoctype($matches)
	{
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	}

	public function getHtml($matches)
	{
		$lang = JFactory::getLanguage();
		$code = substr($lang->getTag(), 0, 2);
		$html = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$code.'" lang="'.$code.'"';
		if ($lang->isRTL()) $html .= ' dir="rtl"';
		$html .= '>';
		return $html;
	}

	public function getNav($matches)
	{
		$nav = str_replace('<nav', '<div', $matches[0]);
		$nav = str_replace('</nav>', '</div>', $nav);
		return $nav;
	}

	public function getSections($matches)
	{
		$class = 'grid_'.$this->columns['main']->size;
		if ($this->columns['main']->push) $class .= ' push_'.$this->columns['main']->push;
		if ($this->columns['main']->pull) $class .= ' pull_'.$this->columns['main']->pull;
		if (strpos($matches[1], 'class=')) {
			preg_match('/class="(.*)"/i', $matches[1], $classes);
			$class .= ' ' . $classes[1];
		}

		if (strpos($matches[1], 'class='))
			$main = preg_replace('/class=\".*\"/iU', 'class="'.$class.'"', $matches[0]);
		else
			$main = preg_replace('/<section/iU', '<div class="'.$class.'"', $matches[0]);

		$main = str_replace('</section>', '</div>', $main);

		return $main;
	}

	public function getAsides($matches)
	{
		// Get id and decide if to even bother
		preg_match('/id=\"(.*)\"/isU', $matches[1], $ids);
		$id = $ids[1];

		$doc = Wright::getInstance();
		if (!$doc->document->countModules($id))
			return;

		$class = 'grid_'.$this->columns[$id]->size;
		if ($this->columns[$id]->push) $class .= ' push_'.$this->columns[$id]->push;
		if ($this->columns[$id]->pull) $class .= ' pull_'.$this->columns[$id]->pull;
		if (strpos($matches[1], 'class=')) {
			preg_match('/class="(.*)"/i', $matches[1], $classes);
			$class .= ' ' . $classes[1];
		}

		if (strpos($matches[1], 'class='))
			$sidebar = preg_replace('/class=\".*\"/iU', 'class="'.$class.'"', $matches[0]);
		else
			$sidebar = preg_replace('/<aside/iU', '<div class="'.$class.'"', $matches[0]);

		$sidebar = str_replace('</aside>', '</div>', $sidebar);

		return $sidebar;
	}

	public function getFooter($matches)
	{
		$class = 'footer';

		if (strpos($matches[1], 'class=')) {
			preg_match('/class="(.*)"/i', $matches[1], $classes);
			$class .= ' ' . $classes[1];
		}

		$footer = $matches[0];

		if (strpos($matches[1], 'class='))
			$footer = preg_replace('/class=\".*\"/iU', 'class="'.$class.'"', $footer);

		$footer = preg_replace('/<footer/iU', '<div', $footer);

		$footer = str_replace('</footer>', '</div>', $footer);

		return $footer;
	}

	public function getHeader($matches)
	{
		$class = 'header';

		if (strpos($matches[1], 'class=')) {
			preg_match('/class="(.*)"/i', $matches[1], $classes);
			$class .= ' ' . $classes[1];
		}

		$header = $matches[0];

		if (strpos($matches[1], 'class='))
			$header = preg_replace('/class=\".*\"/iU', 'class="'.$class.'"', $header, 1);

		$header = preg_replace('/<header/iU', '<div', $header);

		$header = str_replace('</header>', '</div>', $header);

		return $header;
	}
}