<?php
/*
 * This file is part of the System Bundles Dinnovos.
 *
 * (c) Jorge Gaitan <webmaster@dinnovos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yulois;

class Config
{
	private $config = array();

	public function loadConfigGlobal()
	{
		$this->config['project'] = require YS_APP . 'config/project.cf.php';
		$this->config['db'] = require YS_APP . 'config/db.cf.php';
	}

	public function get( $file, $key = null, $default = -1)
	{
		if ( isset( $this->config[$file] ) )
		{
			if($key && isset( $this->config[$file][$key] ))
			{
				return $this->config[$file][$key];
			}

			return $this->config[$file];
		}

		if ( $default == -1 )
			throw new \Exception("No se encontr&oacute; la clave de configuraci&oacute;n <b>$key</b>");

		return $default;
	}
}