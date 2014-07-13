<?php
defined('_JEXEC') or die;

if (!class_exists('JFormFieldSjHeading')){
	class JFormFieldSjHeading extends JFormField{
		public function getInput(){
			return '';
		}
		public function getLabel(){
			return '<label style="text-align:center;width: 100%; max-width: 100%; padding: 5px 0 0 0; border-bottom: solid 1px #003399; color: #003399; font-weight: bold;">' . $this->element['label'] . '</label>';
		}		
	};
}