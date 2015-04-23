<?php
/**
 * This file is part of the Yulois Framework.
 *
 * (c) Jorge Gaitan <info.yulois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ys_Csfr
 * 
 * @author Jorge Gaitan
 */

namespace Yulois\Form\Fields;

Class Csfr extends \Yulois\Form\Field
{
	public function __construct()
	{
		$this->template = null;
		$this->is_hidden = true;
	}

	public function valid()
	{
		return true;
	}

	public function renderField()
	{
		if(!$this->is_display)
			return '';

		$format = $this->name_form . '[' . $this->name . ']';
		$id = $this->name_form . '_' . $this->name;

		return \Yulois\Helper\FormHtml::hidden($format, $this->value, $this->max_length, array(
				'id' => $id,
				'class' =>  $this->getClassCss()
			),
			$this->other_attributes
		);
	}

}
