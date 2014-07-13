<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('F0F_INCLUDED') or die;

/**
 * Form Field class for F0F
 * Renders the row ordering interface checkbox in browse views
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class F0FFormFieldOrdering extends JFormField implements F0FFormField
{
	protected $static;

	protected $repeatable;

	/** @var   F0FTable  The item being rendered in a repeatable form field */
	public $item;

	/** @var int A monotonically increasing number, denoting the row number in a repeatable view */
	public $rowid;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'static':
				if (empty($this->static))
				{
					$this->static = $this->getStatic();
				}

				return $this->static;
				break;

			case 'repeatable':
				if (empty($this->repeatable))
				{
					$this->repeatable = $this->getRepeatable();
				}

				return $this->repeatable;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Method to get the field input markup for this field type.
	 *
	 * @since 2.0
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		throw new Exception(__CLASS__ . ' cannot be used in input forms');
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic()
	{
		throw new Exception(__CLASS__ . ' cannot be used in single item display forms');
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		if (!($this->item instanceof F0FTable))
		{
			throw new Exception(__CLASS__ . ' needs a F0FTable to act upon');
		}

		$html = '';

		$view = $this->form->getView();

		$ordering = $view->getLists()->order == 'ordering';

		if (!$view->hasAjaxOrderingSupport())
		{
			// Ye olde Joomla! 2.5 method
			$disabled = $ordering ? '' : 'disabled="disabled"';
			$html .= '<span>';
			$html .= $view->pagination->orderUpIcon($this->rowid, true, 'orderup', 'Move Up', $ordering);
			$html .= '</span><span>';
			$html .= $view->pagination->orderDownIcon($this->rowid, $view->pagination->total, true, 'orderdown', 'Move Down', $ordering);
			$html .= '</span>';
			$html .= '<input type="text" name="order[]" size="5" value="' . $this->value . '" ' . $disabled;
			$html .= 'class="text-area-order" style="text-align: center" />';
		}
		else
		{
			// The modern drag'n'drop method
			if ($view->getPerms()->editstate)
			{
				$disableClassName = '';
				$disabledLabel = '';

				$hasAjaxOrderingSupport = $view->hasAjaxOrderingSupport();

				if (!$hasAjaxOrderingSupport['saveOrder'])
				{
					$disabledLabel = JText::_('JORDERINGDISABLED');
					$disableClassName = 'inactive tip-top';
				}

				$class = $ordering ? 'order-enabled' : 'order-disabled';

				$html .= '<div class="' . $class . '">';
				$html .= '<span class="sortable-handler ' . $disableClassName . '" title="' . $disabledLabel . '" rel="tooltip">';
				$html .= '<i class="icon-menu"></i>';
				$html .= '</span>';

				if ($ordering)
				{
					$html .= '<input type="text" name="order[]" size="5" class="input-mini text-area-order" ';

					if (!$hasAjaxOrderingSupport)
					{
						$html .= 'disabled="disabled" ';
					}

					$html .= 'value="' . $this->value . '"  class="' . 'input-mini text-area-order' . ' " />';
				}

				$html .= '</div>';
			}
			else
			{
				$html .= '<span class="sortable-handler inactive" >';
				$html .= '<i class="icon-menu"></i>';
				$html .= '</span>';
			}
		}

		return $html;
	}
}
