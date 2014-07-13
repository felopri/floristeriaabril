<?php
/**
* @file
* @brief    sigplus Image Gallery Plus color selection control
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2010 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2010 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.form.formfield');

/**
* Renders a control for choosing CSS border parameters.
* This class implements a user-defined control in the administration backend.
*/
class JFormFieldColor extends JFormField {
	protected $type = 'Color';

	public function getInput() {
		$class = ( isset($this->element['class']) ? (string)$this->element['class'] : 'inputbox' );

		// add script declaration to header
		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/plugins/content/sigplus/fields/jscolor/jscolor.js');

		// add control to page
		$ctrlid = str_replace(array('[',']'), '', $this->name);
		return '<input type="text" class="'. $class .' color" name="'. $this->name .'" id="'. $ctrlid .'" value="'. $this->value .'" />';
	}
}

