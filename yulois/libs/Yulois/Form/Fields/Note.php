<?php

namespace Yulois\Form\Fields;

Class Note extends \Yulois\Form\Field
{
	/* Campos para texto sin etiquetas html */
	public function valid()
	{
		$value = htmlentities( $this->value, ENT_QUOTES, 'UTF-8');

		$this->value = str_replace("\n", "<br />", $value);

		if( $this->pattern )
		{
			if (preg_match('/'.$this->pattern.'/', $this->value))
			{
				return true;
			}

			return false;
		}

		return true;
	}

	public function renderField()
	{
		if(!$this->is_display)
			return '';
		
		$format = $this->name_form . '[' . $this->name . ']';
		$id = $this->name_form . '_' . $this->name;

		return \Yulois\Helper\FormHtml::textarea($format, $this->value, 40, 10, array(
					'id' => $id,
					'class' => $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				),
                $this->other_attributes
        );
	}
}