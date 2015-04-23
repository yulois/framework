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

use yulois\Exception\Ys_Exceptions;
use yulois\Exception\Ys_Error;

Class Foreign extends \Yulois\Form\Field
{
	protected $options = array();
	protected $default = null;
	protected $type_field_tag = 'select';
	protected $type_relation = null;
	protected $name_model_relation = null;
	protected $definition = array();

	public function valid()
	{
		//$_r = \Ys_kernel::model()->exist($this->name_model_relation, $this->value);
		
		return true;
	}

	public function renderField()
	{
		if(!$this->is_display)
			return '';
		
		$format = $this->name_form . '[' . $this->name . ']';
		$id = $this->name_form . '_' . $this->name;

		$options = $this->options;
		
		if(count($options) == 0)
		{
			$options = \AppKernel::db( $this->name_model_relation )->fetchForOptions();
		}

		return \Yulois\Helper\FormHtml::select($format, $options, $this->value, null, array(
					'id' => $id,
					'class' => $this->getClassCss(),
					'disabled' => $this->isDisabled(),
					'readonly' => $this->isReadonly()
				));
	}
	
	public function setTypeRelation($relation)
	{
		if(!in_array($relation, array(
			'many-to-one',
			'many-to-one-self-referencing'
		)))
		{
			throw new \Exception( 'El tipo de relacion "' . $relation . '" no es valido en el fomulario '.$this->getNameForm() );
		}
		
		$this->type_relation = $relation;
		
		return $this;
	}
	
	public function definitionRelation( $foreign, $definition )
	{
		$this->name_model_relation = $foreign;
		$this->definition = $definition;
		return $this;
	}
	
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}

	public function setDefault($name)
	{
		$this->default = $name;

		return $this;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function setTypeFieldTag($type)
	{
		$this->type_field_tag = $type;

		return $this;
	}
}