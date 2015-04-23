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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

Class Action
{
	public function execute( Request $request )
	{
		$I18n = \AppKernel::get('i18n');

		$attributes_request = $request->attributes->all();

		if( (isset( $attributes_request['ajax'] ) && $attributes_request['ajax'] == true) && !$request->isXmlHttpRequest() )
		{
			throw new \Exception( 'La peticion debe ser de tipo XmlHttp.' );
		}

		$prepare = $this->prepare( $attributes_request );

		/* Carga la internacionalizacion de la aplicacion y de del bundle */
		$I18n->load(YS_APP.'i18n', $request->getLocale());
		$I18n->load(YS_BUNDLES.$prepare['bundle'].'/config/i18n', $request->getLocale());

		$controller = $prepare['controller'];
		$action = $prepare['action'];

		// Inserta en la peticion el nombre del bundle, controller y action
		// Esta informacion puede ser util en los controladores
		$request->attributes->set('_bundle', $prepare['bundle']);
		$request->attributes->set('_controller', strtolower($prepare['parts'][1]));
		$request->attributes->set('_action', strtolower($prepare['parts'][2]));

		$instanceController = new $controller( $request );

		ob_start();

		$response = $instanceController->preAction();

		// Si devuelve on objecto lo retorna.
		if( $response )
		{
			return $response;
		}

		// Se llama a la accion.
		$response = $instanceController->$action();

		$instanceController->postAction();

		if ( !($response instanceof Response) )
		{
			$response = new Response( ob_get_clean() );
		}

		return $response;
	}

	public function prepare( $attributes )
	{
		$parts = array();

		if( isset( $attributes['_route'] ) && $attributes['_route'] == 'default' )
		{
			$parts[0] = str_replace(' ', '\\', ucwords(str_replace('-', ' ', $attributes['bundle'])));
			$parts[1] = $attributes['module'];
			$parts[2] = $attributes['action'];
		}
		else
		{
			$controller = $attributes['controller'];
			$parts = explode( ':', $controller );
		}

		if( count( $parts ) == 3 )
		{
			$parts[1] = ucfirst($parts[1]);

			$class_controller = "{$parts[0]}\\Controllers\\{$parts[1]}Controller";

			if ( !class_exists( $class_controller ) )
			{
				throw new \Exception( 'No se encontr&oacute; el controlador - <i>' . $class_controller . '</i>' );
			}

			$method_action = "$parts[2]Action";

			if ( !method_exists( $class_controller, $method_action ) )
			{
				throw new \Exception( 'No se encontr&oacute; la acci&oacute;n - <b>' . $method_action . '</b> - en el controlador: <br /> <i>' . $class_controller . '</i>' );
			}

			return array('bundle' => $parts[0], 'controller' => $class_controller, 'action' => $method_action, 'parts' => $parts);
		}

		throw new \Exception("El path al controlador '$controller' no es v&aacute;lida.");
	}
}