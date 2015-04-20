<?php

namespace Yulois\Form\Fields;

Class Password extends \Yulois\Form\Field
{
	protected $min_length = 5;

	/* Solo permite cadenas con caracteres alfanumericos y caracteres especiales sin espacios */
	public function valid()
	{
		// Permite que el password tenga letras en minusculas, mayusculas y numeros.
		$default = \Yulois\Tools\RegularExpression::get('password');

		if($this->pattern)
		{
			$default = $this->pattern;
		}

		if ( preg_match('/'.$default.'/', $this->value) )
		{
			$this->value = \AppKernel::get('user')->encript( $this->value );

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

		return \Yulois\Helper\FormHtml::password($format, null, $this->getMaxlength(), array(
					'id' => $id,
					'class' => $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				));
	}

}