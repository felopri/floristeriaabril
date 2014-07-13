<?php
/**
 * @package Sj Module Tabs
 * @version 2.5
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 * 
 */
defined('_JEXEC') or die;

class JFormFieldPosition extends JFormField
{
	protected function getInput()
	{
		$session = JFactory::getSession();
		$attr = '';
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
		//now get to the business of finding the articles

		$options = array();
		$options[] = JHTML::_('select.option', '', '-------- None select --------');
		foreach ( $this->_getPositions() as $position ){
			$options[] = JHTML::_('select.option',  $position->position, $position->position . ' (' . $position->modules . ')' );
		}
		
		return JHTML::_('select.genericlist',  $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
	}

	private function _getPositions() {
		$db = &JFactory::getDBO();
		$user = &JFactory::getUser();
		$lang = &JFactory::getLanguage();
		$languages = array(
			'*',
			$lang->getTag()
		);

		$query = "
			SELECT p.position, COUNT(p.id) as modules
			FROM #__modules p
			WHERE 
				p.position IS NOT NULL 
				AND p.position <> '' 
				AND p.access IN (" . implode(',', $user->getAuthorisedViewLevels()) . ")
				AND p.client_id = 0				
			GROUP BY p.position
			ORDER BY p.position
		";
		$db->setQuery($query);
		$positions = $db->loadObjectList();
		return $positions;
	}

}
?>