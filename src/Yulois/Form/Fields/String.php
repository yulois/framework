<?php

namespace Yulois\Form\Fields;

Class String extends \Yulois\Form\Field
{
	protected $min_length = 1;

	/* 
	 * Solo permite cadenas con letras, numeros y espacios
	 * Util para validar nombres, segundo nombre, apellidos de personas 
	 */
	public function valid()
	{
		$default = '^[\w\s\/\-\_\&\;\#\.\,]+$';

        // Reemplaza todas las vocales acentuadas
        $value = \Yulois\Tools\String::replaceVowels( $this->value );

		if( $this->pattern )
		{
			$default = $this->pattern;
		}

		if ( preg_match('/'.$default.'/', $value ) )
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
				),
				$this->other_attributes
		);
	}

}