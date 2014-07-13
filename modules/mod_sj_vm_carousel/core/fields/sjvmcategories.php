<?php
/**
 * @package Sj Carousel for Virtuemart
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
 
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

if (!class_exists('JFormFieldSjVmCategories')){
	
	class JFormFieldSjVmCategories extends JFormFieldList{
		protected $categories = null;
		
		public function getInput(){
			if ( $this->vm_require() ){
				$categories = &$this->getCategories();
				if ( !count($categories) ){
					$input = '<div style="margin: 5px 0;float: left;font-size: 1.091em;">You have no category to select.</div>';
				} else {
					$input = parent::getInput();
				}
			} else {
				$input = '<div style="margin: 5px 0;float: left;font-size: 1.091em;">Maybe your component (Virtuemart) has been installed incorrectly. <br/>Please sure your component work properly. <br/>If you still get errors, please contact us via our <a href="http://www.smartaddons.com/forum/" target="_blank">forum</a> or <a href="http://www.smartaddons.com/tickets/" target="_blank">ticket system</a></div>';
			}
			return $input;
		}

		protected function vm_require(){
			if ( !class_exists('VmConfig') ){
				if ( file_exists(JPATH_ADMINISTRATOR.'/components/com_virtuemart/helpers/config.php') ){
					require JPATH_ADMINISTRATOR.'/components/com_virtuemart/helpers/config.php';
				} else {
					$this->error = 'Could not find VmConfig helper';
					return false;
				}
			}
			if ( !class_exists('VmModel') ){
				if ( defined('JPATH_VM_ADMINISTRATOR') && file_exists(JPATH_VM_ADMINISTRATOR.'/helpers/vmmodel.php') ){
					require JPATH_VM_ADMINISTRATOR.'/helpers/vmmodel.php';
				} else {
					$this->error = 'Could not find VmModel helper';
					return false;
				}
			}
			if ( defined('JPATH_VM_ADMINISTRATOR') ){
				JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.'/tables');
			}
			return true;
		}

		protected function getCategories(){
			if ( is_null($this->categories) ){
				$this->categories = array();
				
				// set user language
				// $lang = JFactory::getLanguage();
				// JRequest::setVar( 'vmlang', $lang->getTag() );

				$categoryModel = VmModel::getModel('category');
				$categoryModel->_noLimit = true;
				$categories = $categoryModel->getCategories( 0 );
				if (!count($categories)) return $this->categories;
				
				// render tree
				//usort($categories, create_function('$a, $b', 'return $a->ordering > $b->ordering;'));
		
				$_categories = array();
				$_children = array();
				foreach ($categories as $i => $category){
					$_categories[$category->virtuemart_category_id] = &$categories[$i];
				}
				foreach ($categories as $i => $category){
					$cid = $category->virtuemart_category_id;
					$pid = $category->category_parent_id;
					if (isset($_categories[$pid])){
						if (!isset($_children[$pid])){
							$_children[$pid] = array();
						}
						$_children[$pid][$cid] = $cid;
					}
				}
				if (!count($_categories)) return $this->categories;
				
				$__categories = array();
				$__levels = array();
				foreach ($_categories as $cid => $category){
					$pid = $category->category_parent_id;
					if ( !isset($_categories[$pid]) ){
						$queue = array($cid);
						$_categories[$cid]->level = 1;
						while ( count($queue) > 0 ){
							$qid = array_shift($queue);
							$__categories[$qid] = &$_categories[$qid];
							if (isset($_children[$qid])){
								foreach ($_children[$qid] as $child){
									$_categories[$child]->level = $_categories[$qid]->level + 1;
									array_push($queue, $child);
								}
							}
						}
					}
				}
				$this->categories = $__categories;
			}
			return $this->categories;
		}

		public function getOptions(){
			$options = parent::getOptions();

			// sorted categories
			$categories = $this->getCategories();
			if ( count($categories) ){
				foreach ($categories as $category){
					$multiplier = $category->level - 1;
					$indent = $multiplier ? str_repeat('- - ', $multiplier) : '';
					$value = $category->virtuemart_category_id;
					$text  = $indent.$category->category_name;
					$options[] = JHtml::_('select.option', $value, $text);
				}
			}
			return $options;
		}

	}
}