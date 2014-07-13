<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

$buttons = $this->getButtons();
if(!empty($buttons)) foreach($buttons as $button):

	if (!empty($button['url']))
	{
		$link = 'href = "' . htmlentities($button['url'], ENT_COMPAT, 'UTF-8') . '"';
	}
	else
	{
		$link = 'href="#" onclick="' . htmlentities($button['onclick'], ENT_COMPAT, 'UTF-8') . '"';
	}
	$class = "btn";
	if(!empty($button['types'])) foreach($button['types'] as $type)
	{
		$class .= " btn-$type";
	}
	$iconclass = "";
	if(!empty($button['icons'])) foreach($button['icons'] as $type)
	{
		$iconclass .= " icon-$type";
	}
?>
						<a <?php echo $link ?> class="<?php echo $class ?>">
<?php if(!empty($iconclass)):?>
							<span class="<?php echo $iconclass ?>"></span>
<?php endif; ?>
							<?php echo $button['message']; ?>
						</a>
<?php
endforeach;