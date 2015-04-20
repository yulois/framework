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
 * Ys_Shell
 * 
 * @author Jorge Gaitan
 */

namespace Yulois\Console;

use Symfony\Component\Console\Application;

Class Shell
{
	public function console()
	{
		$bundles = \AppKernel::registryBundles();

		$cli = new Application("---- Yulois Lista de Comandos", YS_VERSION.' ----');

		$cli->add( new Commands\ModelCommand() );
		$cli->add( new Commands\SchemaCommand() );
		$cli->add( new Commands\DatabaseCommand() );
		$cli->add( new Commands\FormsCommand() );

		$cli->run();
	}
	
	public function help()
	{

	}
}
