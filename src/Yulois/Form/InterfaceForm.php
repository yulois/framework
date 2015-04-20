<?php

/*
 * This file is part of the yulois Framework.
 *
 * (c) Jorge Gaitan <jorge@yulois.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ys_InterfaceForm
 * 
 * @author Jorge Gaitan
 */

namespace Yulois\Form;

use Yulois\Form\Field;

Class InterfaceForm implements \ArrayAccess
{
	protected $widgets = array();
	protected $name_form = null;
	protected $method = 'post';
	protected $name_model = null;
	protected $model = null;
	protected $csrf_token = null;
	protected $msg_global_error = null;
	protected $is_valid = true;
	protected $is_multipart = false;
	protected $data = array();
	protected $validators = array();
	protected $Kernel;
	protected $I18n;
	protected $files_uploads = array();
	protected $identifier = null;
	protected $config_fields = array();
	protected $path_upload = null;
	protected $all_errors = array();

	public function __construct( $instance_model = null )
	{
		$this->I18n = \AppKernel::get( 'i18n' );
		
		// token para campo csrf del form
		$this->csrf_token = sha1( get_class($this) );

		// Nombre de la clase formulario
		$this->name_form = basename( str_replace('\\', '/', get_class($this)) );

		// Llama a config() de la clase base
		$this->config();

		// Llama a change() de la clase extends de base
		$this->change();

		// Crea el widget para seguridad de ataque csrf
		$this->setWidget( 'csrf_token', new \Yulois\Form\Fields\Csfr() )->setValue( $this->csrf_token );

		if( $instance_model )
		{
			if( get_class( $instance_model ) == $this->name_model )
			{
				$this->model = $instance_model;
			}
			else
			{
				throw new \Exception( "No es un modelo valido para el formulario ". $this->name_form );
			}
		}

		if( $this->model )
		{
			$widgets = $this->widgets;

			foreach ( $widgets as $name_field => $widget )
			{
				if( isset( $this->model->$name_field ) )
				{
					$widget->setValue( $this->model->$name_field );
				}
			}
		}
	}

	public function offsetSet( $offset, $widget )
	{
		$this->setWidget( $offset, $widget );
	}

	public function offsetExists( $offset )
	{
		return isset( $this->widgets[$offset] );
	}

	public function offsetUnset( $offset )
	{
		unset( $this->widgets[$offset] );
	}

	public function offsetGet( $offset )
	{
		return isset( $this->widgets[$offset]) ? $this->widgets[$offset] : null;
	}

	public function unsetField( $name )
	{
		unset( $this->widgets[$name] );
	}

	/********************************************************************/

	public function setName( $name )
	{
		$this->name_form = $name;
	}

	public function setNameModel( $name )
	{
		$this->name_model = $name;
	}

	public function setWidget( $name, Field $widget )
	{
		$widget->setName( strtolower($name) );
		$widget->setNameForm( $this->name_form );
		$widget->setForm( $this );
		$widget->setI18n( $this->I18n );
		$widget->setConfig( $this->config_fields );
		$widget->setPathUpload( $this->path_upload );

		$this->widgets[ $name ] = $widget;

		return $widget;
	}

	public function setPathUpload( $path )
	{
		$this->path_upload = $path;
	}

	public function setMultipart( $multipart )
	{
		$this->is_multipart = ( is_bool($multipart) ) ? $multipart : false;
	}

	public function setGlobalError($msg)
	{
		$this->msg_global_error = $msg;
	}

	public function setError( $field, $msg )
	{
		$Widgets = $this->widgets;

		if( array_key_exists($field, $Widgets) )
		{
			$this->all_errors[$field] = $msg;
			$this->widgets[$field]->setError( $msg );
			$this->widgets[$field]->setValid( false );
			$this->is_valid = false;
			return;
		}

		throw new \Exception( "El campo '$field' no es v&aacute;lido." );
	}

	/************************************************************************/

	/**
	 * @return Field
	 */
	public function getWidget($name)
	{
		return $this->offsetGet($name);
	}

	/**
	 * @return Field
	 */
	public function getWidgets()
	{
		return $this->widgets;
	}

	public function getCsrfToken()
	{
		return $this->csrf_token;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getGlobalError()
	{
		return $this->msg_global_error;
	}
	
	public function getNameModel()
	{
		return $this->name_model;
	}
	
	public function getModel()
	{
		return $this->model;
	}
	
	public function getNameForm()
	{
		return $this->name_form;
	}
	
	public function getIdentifier()
	{
		return $this->identifier;
	}
	
	public function getData()
	{
		return $this->data;
	}

	public function getPathUpload()
	{
		return $this->path_upload;
	}

	public function getAllErrors()
	{
		return $this->all_errors;
	}

	/************************************************************************/

	public function isMultipart()
	{
		return ($this->is_multipart) ? true : false;
	}
}