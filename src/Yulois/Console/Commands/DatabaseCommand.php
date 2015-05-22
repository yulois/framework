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
 * Ys_CreateCommand
 * 
 * @author Jorge Gaitan
 */

namespace Yulois\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\Yaml\Yaml;

Class DatabaseCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('app:database')
			->setDescription('Crea las tablas en la base de datos desde el Bundle especificado como argumento.')
			->addArgument(
				'namespace',
				InputArgument::REQUIRED,
				'namespace of bundle'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');
		$bundle = $input->getArgument('namespace');

		$bundle = trim($bundle, '/');
		$bundle = trim($bundle, '\\');

		$path_schema = YS_BUNDLES . $bundle . '/storage/schemas/';

		$version = $dialog->ask( $output, PHP_EOL .' Por favor, ingrese la version del esquema que desea utilizar [new]: ', null );

		if( $version === null )
		{
			$version = 'new';
		}
		else
		{
			while( !preg_match('/^([1-9][0-9\.]+[0-9])+$/', $version) )
			{
				$output->writeln( PHP_EOL .' <error>ATENCION: La version no tiene un formato valido, debe ingrear por ejemplo: 1.0</error>' );

				$version = $dialog->ask( $output, PHP_EOL .' Por favor, ingrese la version del esquema que desea utilizar [new]: ', null );

				if( $version === null )
				{
					$version = 'new';
					break;
				}
			}
		}

		if( !is_dir( $path_schema . $version ) )
		{
			$output->writeln( PHP_EOL . " <error>No se encontro el esquema dentro del directorio {$version}/ del bundle {$bundle}</error>" . PHP_EOL );
			exit;
		}

		$this->createDatabase( $input, $output, $path_schema, $path_schema . $version );
	}

	private function createDatabase( $input, $output, $path_schema_base, $path_schema )
	{
		$fs = new Filesystem();
		$dateTime = new \DateTime();

		$output->write( PHP_EOL . " Actualizando la base de datos..." . PHP_EOL.PHP_EOL );

		if( !is_file( $path_schema.'/schema.php' ) )
		{
			$output->writeln( " <error>ATENCION: El esquema no fue creado.</error>" . PHP_EOL );

			return;
		}

        if(is_file($path_schema_base.'current/schema.php'))
        {
            $schema_current = include $path_schema_base.'current/schema.php';
        }
        else
        {
            $schema_current = new \Doctrine\DBAL\Schema\Schema();
        }

		// Se Obtiene el objeto del esquema creado.
		$schema = include $path_schema.'/schema.php';

        $comparator = new \Doctrine\DBAL\Schema\Comparator();
        $schemaDiff = $comparator->compareSchemas( $schema_current, $schema);

		$DriverManager = \AppKernel::db()->getDriverManager();

        /*
		$sm = $DriverManager->getSchemaManager();
		$schema_current = $sm->createSchema();
        */

		//$queries = $schemaDiff->toSaveSql( $DriverManager->getDatabasePlatform() );

        $queries = $schemaDiff->toSql($DriverManager->getDatabasePlatform());

		if(count($queries) == 0)
		{
			$output->writeln( PHP_EOL." <info>No hay nada para actualizar.</info>" );
			return;
		}

		$_q = "set foreign_key_checks = 0;";
		$DriverManager->query( $_q );
		$output->writeln( PHP_EOL." $_q".PHP_EOL );

		foreach($queries as $query)
		{
			$DriverManager->query($query);
			$output->writeln(" - $query");
		}

		$_q = "set foreign_key_checks = 1;";
		$output->writeln(PHP_EOL." $_q".PHP_EOL);
		$DriverManager->query($_q);

        // Compia todos los archivos a current
        $this->mkdir($path_schema_base.'current');
        $fs->copy($path_schema_base.'new/database.sql', $path_schema_base.'current/database.sql', true);
        $fs->copy($path_schema_base.'new/readme.md', $path_schema_base.'current/readme.md', true);
        $fs->copy($path_schema_base.'new/schema.php', $path_schema_base.'current/schema.php', true);
        $fs->copy($path_schema_base.'new/schema.yml', $path_schema_base.'current/schema.yml', true);

		$output->writeln("<info>La base de datos fue actualizada correctamente.</info>");
	}

    private function  mkdir( $path )
    {
        $fs = new Filesystem();

        try
        {
            $fs->mkdir( $path );

            return  true;
        }
        catch (IOException $e)
        {
            echo "Ha ocurrido un error mientras se generaba el directorio: $path";
        }
    }

	private function showErrors( $GeneratorSchema )
	{
		$errors = $GeneratorSchema->getErrors();

		echo <<<EOT

ATENCION: Se encontraron los siguientes errores en el esquema...

EOT;
		foreach ( $errors as $error )
		{
			echo $error . "\n";
		}
		echo "
";
	}
}