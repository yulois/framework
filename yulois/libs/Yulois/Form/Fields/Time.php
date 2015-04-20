<?php

namespace Yulois\Form\Fields;

Class Time extends \Yulois\Form\Field
{
    protected $format = 'H:i:s';

	/*
	 * Campo para hora con formato hh:mm:ss
	 */
	public function __construct()
	{
		$this->value = date('h:m:s', time());
	}

	public function valid()
	{
		$default = '^[0-9]{2}\:[0-9]{2}\:[0-9]{2}$';

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

		return \Yulois\Helper\FormHtml::input($format, \Yulois\Tools\Date::format($this->value, $this->format), $this->max_length, array(
					'id' => $id,
					'class' => $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				));
	}

    public function setFormatDate( $format )
    {
        $this->format = $format;
    }
}