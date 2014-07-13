<?php


defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');


class JFormFieldBTLAbout extends JFormField{

	public function getTemplateName(){
		$templateName 	= explode( DS, str_replace( array( '\elements', '/elements' ), '', dirname(__FILE__) ) );
		$templateName 	= $templateName [ count( $templateName ) - 1 ];
		return $templateName;
	}


	protected function getInput(){
		$doc 				= JFactory::getDocument();
		$templateName		= $this->getTemplateName();
		$doc->addStyleSheet(JURI::root().'templates/'.$templateName.'/admin/css/btl_admin.css');
		$doc->addScript(JURI::root().'templates/'.$templateName.'/admin/js/btl_slider.js');
		$doc->addScript(JURI::root().'templates/'.$templateName.'/admin/js/btl_admin.js');
	}
}
