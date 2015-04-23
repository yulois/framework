<?php
/**
 * This file is part of the Yulois Framework.
 *
 * (c) Jorge Gaitan <info.yulois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yulois\Form\Fields;

Class Email extends \Yulois\Form\Field
{
	protected $min_length = 7;

	/* Para validar direcciones de emails */
	public function valid()
	{
		$default = \Yulois\Tools\RegularExpression::get('email');

		if($this->pattern)
		{
			$default = $this->pattern;
		}

		if ( preg_match('/'.$default.'/', $this->value) )
		{
			return true;
		}

		return false;
	}

	public function renderField()
	{
		if(!$this->is_display)
			return '';
		
		$format = $this->name_form . '[' . $this->name . ']';
		$id = $this->name_form . '_' . $this->name;

		return \Yulois\Helper\FormHtml::input($format, $this->value, $this->max_length, array(
					'id' => $id,
					'class' => $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				));
	}

}