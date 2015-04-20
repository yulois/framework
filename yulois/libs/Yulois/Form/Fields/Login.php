<?php

namespace Yulois\Form\Fields;

Class Login extends \Yulois\Form\Field
{
	protected $min_length = 5;

	/* Solo permite cadenas con caracteres y numeros sin espacios */
	public function valid()
	{
		$default = '^[a-z0-9]+$';

		if($this->pattern)
		{
			$default = $this->pattern;
		}

		if ( preg_match( '/'.$default.'/', $this->value ) )
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

		return \Yulois\Helper\FormHtml::input( $format, $this->value, $this->max_length, array(
					'id' => $id,
					'class' => $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				) );
	}

}