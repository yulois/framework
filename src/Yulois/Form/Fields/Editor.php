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

Class Editor extends \Yulois\Form\Field
{
	/* Para guardar texto con etiquetas html */
	public function valid()
	{
		$patrones = array('(<script>)', '(</script>)', '(javascript)', '(onclick)', '(ondblclick)', '(onmousedown)',
			'(onmouseup)', '(onmouseover)', '(onmousemove)', '(onmouseout)', '(onkeypress)', '(onkeydown)', '(onkeyup)'
		);

		$this->value = preg_replace($patrones, '', $this->value);

		if($this->pattern)
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
					'class' => 'editor ' . $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				));
	}

	public function setMinLength($length = 0, $msg = null)
	{
		return $this;
	}

	public function setMaxLength($length = 255, $msg = null)
	{
		return $this;
	}

}