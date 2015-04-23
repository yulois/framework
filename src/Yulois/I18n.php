<?php
/**
 * This file is part of the Yulois Framework.
 *
 * (c) Jorge Gaitan <info.yulois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ys_I18n
 * 
 * @author Jorge Gaitan
 */

namespace Yulois;

Class I18n
{
	private $data = array();

	public function __construct()
	{

	}

	public function get($key, $default = -1)
	{
		if (isset($this->data[$key]))
		{
			return htmlentities($this->data[$key], ENT_QUOTES, 'UTF-8');
		}

		if ($default == -1)
			throw new \Exception("No existe la clave para traducir '$key'");

		return htmlentities($default, ENT_QUOTES, 'UTF-8');
	}

	public function load( $path, $language )
	{
		if (is_dir($path . '/' . $language))
		{
			/*
			 * Obtiene los archivos del directorio
			 */
			$files = \Yulois\Tools\Util::getFilesPath( $path . '/' . $language, 'i18n.php' );

			foreach ($files as $path_file)
			{
				if (!is_file($path_file))
					throw new \Exception("No existe el archivo de configuraci&oacute;n '$path_file'");

				$_a = require $path_file;
				
				$this->data = array_merge($this->data, $_a);		
			}
		}
	}

}