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
 * Ys_Shell
 * 
 * @author Jorge Gaitan
 */

namespace Yulois\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

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

    public function execute(Command $command, array $input, array $options = array())
    {
        // set the command name automatically if the application requires
        // this argument and no command name was passed
        if (!isset($input['command'])
            && (null !== $application = $this->command->getApplication())
            && $application->getDefinition()->hasArgument('command')
        ) {
            $input = array_merge(array('command' => $this->command->getName()), $input);
        }

        $this->input = new ArrayInput($input);
        if (isset($options['interactive'])) {
            $this->input->setInteractive($options['interactive']);
        }

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        if (isset($options['decorated'])) {
            $this->output->setDecorated($options['decorated']);
        }
        if (isset($options['verbosity'])) {
            $this->output->setVerbosity($options['verbosity']);
        }

        return $this->statusCode = $command->run($this->input, $this->output);
    }

	public function help()
	{

	}
}
