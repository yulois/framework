<?php
/**
 * This file is part of the Yulois Framework.
 *
 * (c) Jorge Gaitan <info.yulois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yulois;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Request;
use Yulois\Form\Form;
use Yulois\Orm\Db;

Class Kernel
{
	private static $services = array();
	protected static $definitions = array();

	public static function execute( Request $request )
	{
		if ( !in_array(YS_ENVIRONMENT, array('dev', 'prod', 'shell')) )
		{
			throw new \Exception("El entorno \"".YS_ENVIRONMENT."\" no est&aacute; permitido");
		}

		// Carga la configuracion global
		self::get('config')->loadConfigGlobal();

		if ( YS_ENVIRONMENT == 'shell' )
		{
			$Shell = self::get('shell');

			$Shell->console();

			return;
		}

		self::setService('request', $request);

		// Ejecuta la accion de la peticion y devuelve un objecto Response
		return self::get('action', true)->execute( $request );
	}

	public static function setService( $name, $instance )
	{
		if( isset(self::$services[$name]) )
		{
			throw new \Exception("El nombre del servicio ya existe en el contenedor");
		}

		self::$services[$name] = $instance;
	}

	public static function get( $service, $new = false, $param1 = null, $param2 = null, $param3 = null )
	{
		if( $new )
		{
			if(isset(self::$definitions[$service]))
			{
				return self::$services[$service] = new self::$definitions[$service]($param1, $param2, $param3);
			}
		}
		else
		{
			if(isset(self::$services[$service]))
			{
				return self::$services[$service];
			}

			if(isset(self::$definitions[$service]))
			{
				return self::$services[$service] = new self::$definitions[$service]($param1, $param2, $param3);
			}
		}

		throw new \Exception("El servicio \"{$service}\" no est&aacute; definido en el contenedor");
	}

	/**
	 * @return Db
	 */
	public static function db( $namespace = null, $connection = 'default' )
	{
		return self::get('db', true, $namespace, $connection);
	}

	/**
	 * @return Form
	 */
	public static function form( $namespace_form = null, $instance_model = null )
	{
		if ( $namespace_form )
		{
			return new $namespace_form( $instance_model );
		}

		throw new \Exception( "La clase para formulario es invalida." );
	}

	/**
	 * @return Filesystem
	 */
	public static function fileSystem()
	{
		return new Filesystem();
	}

	public static function listDefinitions()
	{
		self::$definitions = array(
			'action'			=> 'Yulois\Action',
			'view'				=> 'Yulois\View',
			'config'			=> 'Yulois\Config',
			'shell'				=> 'Yulois\Console\Shell',
			'db'				=> 'Yulois\Orm\Db',
			'validate_schema'	=> 'Yulois\Generator\ValidateSchema',
			'generate_class'	=> 'Yulois\Generator\GenerateClass',
			'i18n'				=> 'Yulois\I18n',
			'user'				=> 'Yulois\Session\User',
			'image'				=> 'Yulois\Tools\Image',
			'paypal'		    => 'Yulois\Paypal',
		);
	}
}
