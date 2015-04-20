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
 * Util
 * 
 * @author Jorge Gaitan
 */

namespace Yulois\Tools;

Class Util
{
	static function clearMagicQuotes()
	{
		if(get_magic_quotes_gpc())
		{
			if(count($_POST))
			{
				$_POST = clearslashes($_POST);
			}

			if(count($_GET))
			{
				$_GET = clearslashes($_GET);
			}
		}
	}

	static function clearslashes( $array )
	{
		return is_array( $array ) ? array_map( 'clearslashes', $array ) : stripslashes( $array );
	}

	static function getFilesPath( $path = null, $only_ext = null, $only_name = false  )
	{
		$path = rtrim( $path, '/' ) . '/';

		if ( !$path )
			return array();

		if ( !is_dir( $path ) )
			throw new \Exception( "No existe la ruta '$path'" );

		$files = array();

		//$dir = dir($path);
		$dh = opendir( $path );

		while ( $file = readdir( $dh ) )
		{
			if ( $file != "." && $file != ".." )
			{
				if( $only_ext )
				{
					if( preg_match("/({$only_ext})$/", $file) )
					{
						$files[] = ($only_name) ? $file : $path . $file;
					}
				}
				else
				{
					$files[] = ($only_name) ? $file : $path . $file;
				}
			}
		}

		return $files;
	}

	static function parsetArrayToString( $array )
	{
		if ( !is_array( $array ) )
			throw new Exception( "El parametro de parsetArrayToString debe ser un array" );

		$str = 'array(';

		foreach ( $array as $key => $value )
		{
			$str .= "'$key'=>'$value', ";
		}

		$str = rtrim( $str, ', ' ) . ')';

		return $str;
	}

	static function orderArray($toOrderArray, $field, $inverse = false)
	{
		$position = array();
		$newRow = array();
		foreach ($toOrderArray as $key => $row)
		{
			$position[$key]  = $row[$field];
			$newRow[$key] = $row;
		}

		if ($inverse)
		{
			arsort($position);
		}
		else
		{
			asort($position);
		}

		$returnArray = array();
		foreach ($position as $key => $pos)
		{
			$returnArray[] = $newRow[$key];
		}

		return $returnArray;
	}

	static function hash( $id, $str = 'z6i5v36h3F5', $position = 5, $prefix = '' )
	{
		$hash = sha1( $str.$id );

		// Le resto 1
		$position--;

		$len = strlen($prefix);

		$position = (int)$position - (int)$len;

		$hash_pre = substr($hash, 5, $position);

		return substr( $prefix.$hash_pre.$id.$hash,0,30 );
	}

	static function isFile( $path, $file )
	{
		if( is_file( $path.$file ) )
		{
			return true;
		}

		return false;
	}
}