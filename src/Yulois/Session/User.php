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
 * User
 * 
 * @author Jorge Gaitan
 */

namespace Yulois\Session;

use Symfony\Component\HttpFoundation\Session\Session;

Class User extends Session
{
	public function encript( $string )
	{
		$token = \AppKernel::get('config')->get('project', 'token');

		$_hash = sha1( $string . $token . substr($string, 1, 3) );

		return $_hash.':'.substr($_hash, 1, 4); 
	}

	public function createTokenSession()
	{
		return $this->_tokenSession();
	}

	public function isValidTokenSession( $token )
	{
		$_token = $this->_tokenSession();

		if ( $token == $_token )
		{
			return true;
		}

		return false;
	}

	public function getBagTemporary()
	{
		return $this->getBag('temporary');
	}

	private function _tokenSession()
	{
		$token = \AppKernel::get('config')->get('project', 'token');

		return sha1( 'f42xG51gd'.$_SERVER['HTTP_USER_AGENT'].$token );
	}
}