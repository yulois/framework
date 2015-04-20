<?php
/*
 * This file is part of the System Bundles Dinnovos.
 *
 * (c) Jorge Gaitan <webmaster@dinnovos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Debug\Debug;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;

use \Yulois\Session\TemporaryAttributeBag;

define('VND_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

// Se utiliza el autoloader que viene con Composer
$loader = require_once VND_ROOT.'autoload.php';

$loader->set( 'Yulois\\', array( YS_VND.'yulois/libs/' ) );
$loader->set( 'Main\\', array( YS_APP ) );

if( !defined('YS_ENVIRONMENT') )
{
	define ('YS_ENVIRONMENT', 'prod');
}

define( 'YS_VERSION', '1.0' );

define( 'YS_CACHE', YS_APP . 'cache/' );
define( 'YS_CORE', YS_VND . 'yulois/' );
define( 'YS_EXT_TEMPLATE', '.twig' );

if ( YS_ENVIRONMENT == 'prod' )
{
	ini_set( 'display_errors', 0 );
	$debug = false;
}
else
{
	Debug::enable();
	$debug = true;
}

define('YS_DEBUG', $debug);

require_once YS_APP . '/AppKernel.php';

// Carga la definicion de las clases
\AppKernel::listDefinitions();

// Inicia la session
$User = \AppKernel::get('user');

$TemporaryStorage = new TemporaryAttributeBag();
$User->registerBag( $TemporaryStorage );

$User->start();

// Crea el objeto Request
$request = Request::createFromGlobals();

// Crea el objecto Kernel del Framework y obtiene la lista de los bundles instalados

$bundles = \AppKernel::registryBundles();

$routes = new RouteCollection();

// Carga todas las rutas de los bundles instalados.
foreach( $bundles as $bundle )
{
	// Registra el namespace del Bundle.
	$loader->set( $bundle, array( YS_BUNDLES ) );

	$file_routes = str_replace('\\', '/', YS_BUNDLES.$bundle.'config/routes.cf.php' );;

	if( is_file( $file_routes ) )
	{
		include $file_routes;
	}
}

include YS_APP.'config/routes.cf.php';

$requestContext = new RequestContext();

// El contexto se actualiza con la info del request
$requestContext->fromRequest( $request );

// Se encarga de obtener la ruta que coincide con el patron de la url de la petición
$matcher = new UrlMatcher( $routes, $requestContext );
$url_generator = new UrlGenerator( $routes, $requestContext );

try
{
	$parameters = $matcher->match( $request->getPathInfo() );
	$request->attributes->add( $parameters );
	$request->setDefaultLocale('es');

	\AppKernel::setService('url_generator', $url_generator );
	\AppKernel::setService('route', $routes );

	// Devuelve un objeto Response.
	$response = \AppKernel::execute( $request );
}
catch ( ResourceNotFoundException $e )
{
	if( YS_DEBUG )
	{
		throw new ResourceNotFoundException("No existe una ruta para la url: ". $request->getPathinfo(), $e->getCode(), $e); //en desarrollo mostramos la excepción
	}
	else
	{
		$response = new Response('Internal Server Error', 501);
	}
}

$response->send();

$TemporaryStorage->checkStatus();