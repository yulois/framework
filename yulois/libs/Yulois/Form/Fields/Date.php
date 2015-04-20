<?php

namespace Yulois\Form\Fields;

Class Date extends \Yulois\Form\Field
{
    protected $format = 'Y-m-d';

	public function valid()
	{
		$default = '^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$';

		if($this->pattern)
		{
			$default = $this->pattern;
		}

		if (preg_match('/'.$default.'/', $this->value))
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

        $value = ($this->value) ? \Yulois\Tools\Date::format($this->value, $this->format) : '';

		return \Yulois\Helper\FormHtml::input($format, $value, $this->max_length, array(
					'id' => $id,
					'class' => $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				), $this->other_attributes );
	}

    public function setFormatDate( $format )
    {
        $this->format = $format;
    }

}