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

use PHPMailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Detection\MobileDetect;

class Controller
{
    /**
     * @var User
     * @see User
     */
    protected $User;

    /**
     * @var Request
     * @see Request
     */
    protected $Request;

    /**
     * @var Response
     * @see Response
     */
    protected $Response;

    /**
     * @var View
     * @see View
     */
    protected $View;

    /**
     * @var I18n
     * @see I18n
     */
    protected $I18n;

    /**
     * @var Config
     * @see Config
     */
    protected $Config;

    /**
     * @var MobileDetect
     * @see MobileDetect
     */
    protected $MobileDetect;

    public function __construct( $request = null )
    {
        $this->Request = ($request && ($request instanceof Request)) ? $request : \AppKernel::get('request');

        $this->User = \AppKernel::get('user');

        $this->View = \AppKernel::get('view');
        $this->Config = \AppKernel::get('config');
        $this->I18n = \AppKernel::get('i18n');

        $this->MobileDetect = new MobileDetect;
    }

    public function preAction()
    {

    }

    public function postAction()
    {

    }

    /*
     * @return Request
     */
    public function getRequest()
    {
        return $this->Request;
    }

    /**
     * @return Response
     */
    public function getResponse( $content = '', $status = 200, $headers = array() )
    {
        return new Response( $content, $status, $headers );
    }

    public function getParameters($key)
    {
        $attributes = $this->Request->attributes->all();

        if( array_key_exists($key, $attributes) )
        {
            return (string)$attributes[$key];
        }

        return null;
    }

    /*
     * @return User
     */
    public function getUser()
    {
        return $this->User;
    }

    /*
     * @return View
     */
    public function getView()
    {
        return $this->View;
    }

    /*
     * @return I18n
     */
    public function getI18n()
    {
        return $this->I18n;
    }

    /*
     * @return Config
     */
    public function getConfig()
    {
        return $this->Config;
    }

    /**
     * @return Mobile_Detect
     */
    public function getMobileDetect()
    {
        return $this->MobileDetect;
    }

    /**
     * @param bool $debug
     * @return PHPMailer
     */
    public function getPHPMailer( $debug = false )
    {
        $path = VND_ROOT.'phpmailer/phpmailer/class.phpmailer.php';

        if( is_file( $path ) )
        {
            require_once $path;

            return new PHPMailer( $debug );
        }

        return null;
    }

    public function getRender( $template, $data = array() )
    {
        return $this->View->render( $template, $data );
    }

    public function render( $template, $data = array() )
    {
        $content = $this->View->render( $template, $data );

        return new Response( $content );
    }

    public function buildUrl( $route, $parameters = array() )
    {
        return \AppKernel::get('url_generator')->generate( $route, $parameters);
    }

    /**
     * @param $controller
     * @param array $path
     * @param array $query
     * @return Response
     */
    public function forward( $controller, array $path = array(), array $get = array(), $post = array() )
    {
        $path['controller'] = $controller;

        $subRequest = $this->Request->duplicate( $get, $post, $path );

        return \AppKernel::get('action')->execute( $subRequest );
    }

    protected function forward404Unless( $value = null )
    {
        if ( empty($value) )
        {
            $this->forward404();
        }
    }

    protected function forward404If( $value )
    {
        if ( $value )
        {
            $this->forward404();
        }
    }

    protected function forward404()
    {
        throw new ResourceNotFoundException( "Pagina no encontrada" );
    }

    public function redirectResponse( $url, $status = 302 )
    {
        return new RedirectResponse( $url, $status );
    }

    public function jsonResponse( $data )
    {
        $response = new JsonResponse();
        $response->setData( $data );

        return $response;
    }

    public function isPost()
    {
        $post = $this->Request->request->all();

        return (count( $post )) ? true : false;
    }

    public function clearVar($value, $encoding='UTF-8')
    {
        return htmlentities(strip_tags($value), ENT_QUOTES, $encoding);
    }

    public function clearTextarea( $value )
    {
        // Inserta saltos de línea HTML antes de todas las nuevas líneas de un string
        return nl2br($this->clearVar($value));
    }

    public function getBaseUrl($is_secure = false)
    {
        if( $is_secure )
        {
            return "https://{$_SERVER['HTTP_HOST']}";
        }

        return "http://{$_SERVER['HTTP_HOST']}";
    }

    public function slug($string)
    {
        $string = \Yulois\Tools\String::replaceVowels($string);

        $string = preg_replace('/[^a-zA-Z0-9\_\-\.]+/', '-', strtolower($string));
        $string = trim($string, '-');
        $string = trim($string, '_');
        $string = trim($string, '.');

        return $string;
    }
}