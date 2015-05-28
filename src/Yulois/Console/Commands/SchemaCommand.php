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
use Symfony\Component\Filesystem\Exception\IOException;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Yulois\Tools\Util;

Class SchemaCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('app:schema')
			->setDescription('Crea el esquema desde el Bundle especificado como argumento.')
			->addArgument(
				'action',
				InputArgument::OPTIONAL,
				'create o freeze',
				'create'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$action = $input->getArgument('action');

		if( !in_array($action, array('create', 'freeze') ) )
		{
			$output->writeln( PHP_EOL . 'El parametro "action" debe ser create o freeze. Por defecto es create.' . PHP_EOL );

			exit;
		}

        $bundles = \AppKernel::registryBundles();
        $_schema = array();

        foreach( $bundles as $bundle )
        {
            $dir_schema = str_replace('\\', '/', YS_BUNDLES.$bundle.'config/schema');

            if(is_dir($dir_schema))
            {
                $files = Util::getFilesPath($dir_schema, 'yml');

                /* Une todos los esquemas en un solo array */
                foreach ( $files as $yml )
                {
                    // Concatena el esquema de cada archivo conseguido
                    $_schema = array_merge( $_schema, Yaml::parse( $yml ) );
                }
            }
        }

		$ValidateSchema = \AppKernel::get( 'validate_schema' );

		/* Valida los archivos *.yml y muestra los posibles errores */
		if ( !$ValidateSchema->isValid( $_schema ) )
		{
			$this->showErrors( $ValidateSchema );

			exit;
		}

		/* Obtiene el array() del esquema */
		$schema = $ValidateSchema->getSchema();

		switch( strtoupper($action) )
		{
			case 'CREATE':

				$this->createSchema( $input, $output, $schema );
				break;

			case 'FREEZE':

				$this->freezeSchema( $input, $output, $schema, $action );
				break;
		}
	}

	private function createSchema( $input, $output, $schema )
	{
		$GenerateClass = \AppKernel::get( 'generate_class' );
		$dialog = $this->getHelperSet()->get('dialog');
		$fs = new Filesystem();
		$dateTime = new \DateTime();

		$output->write(PHP_EOL . " Creando el esquema..." . PHP_EOL.PHP_EOL);

		$path_schema = YS_APP . 'storage/schemas/';

		if(is_file($path_schema.'current/schema.php'))
		{
			if ( !$dialog->askConfirmation( $output, ' <question>Ya existe una version esquema, desea reemplazarlo [n]?</question> ', false) )
			{
				$output->writeln( " <error>ATENCION: El esquema no fue creado.</error>" . PHP_EOL );

				return;
			}
		}

		// Crea el directorio storage/schema/current
		$this->mkdir( $path_schema.'current' );

		// vuelca el arreglo PHP a YAML
		$dumper = new Dumper();
		$content_yaml = $dumper->dump( $schema );

		// Crea el archivo yml dentro de storage/schema/current.
		$fs->dumpFile( $path_schema.'current/schema.yml', $content_yaml);
		$output->writeln( PHP_EOL." <info>- Se genero el archivo schema.yml correctamente.</info>" );

		// Crea el archivo yml dentro de storage/schema/current.
		$content_readme = 'Creado: '.$dateTime->format('Y-m-d H:i:s');
		$fs->dumpFile($path_schema.'current/readme.md', $content_readme);
		$output->writeln(" <info>- Se genero el archivo readme.md correctamente.</info>");

		$GenerateClass->setTemplate( 'Doctrine' );
		$GenerateClass->create( $path_schema.'current/schema', $schema );
		$output->writeln(" <info>- Se genero el archivo schema.php correctamente.</info>" );

		// Se Obtiene el objeto del esquema creado.
		$schema = include $path_schema.'current/schema.php';

		// Se crea el archivo .sql que contendra la estructura del esquema para la base de datos.
		$querys = $schema->toSql(\AppKernel::db()->getDriverManager()->getDatabasePlatform());

		$sql = "";
		foreach( $querys as $query )
		{
			$sql .= "$query;\n";
		}

		$fs->dumpFile($path_schema.'current/database.sql',$sql);
		$output->writeln(" <info>- Se genero el archivo database.sql correctamente.</info>");

		$output->writeln( PHP_EOL." <info>EL esquema fue creado correctamente en: ".YS_SYSTEM."app/storage/schemas/</info>" );
	}

	private function freezeSchema( $input, $output, $schema )
	{
		$dialog = $this->getHelperSet()->get('dialog');
        $path_schema = YS_APP . 'storage/schemas/';
		$first = true;
		$files_schema = array(
			$path_schema.'current/database.sql',
			$path_schema.'current/readme.md',
			$path_schema.'current/schema.php',
			$path_schema.'current/schema.yml'
		);

		$fs = new Filesystem();

		do
		{
			if( $first )
			{
				$version = $dialog->ask( $output, PHP_EOL .' Por favor, ingrese la version del esquema: ', null );
			}
			else
			{
				$output->writeln( PHP_EOL .' <error>ATENCION: La version no tiene un formato valido, debe ingrear por ejemplo: 1.0</error>'. PHP_EOL );
				$version = $dialog->ask( $output, PHP_EOL .' Por favor, ingrese la version del esquema: ', null );
			}

			$first = false;

			while( preg_match('/^([1-9][0-9\.]+[0-9])+$/', $version) && is_file($path_schema.$version.'/schema.php') )
			{
				$output->write( PHP_EOL .' <error>ATENCION: Ya existe un esquema con la misma version.</error>'. PHP_EOL );
				$first = true;
				$version = null;
			}
		}
		while( !preg_match('/^([1-9][0-9\.]+[0-9])+$/', $version) );

		$exists = $fs->exists( $files_schema );

		if( !$exists )
		{
			$output->writeln( PHP_EOL .' <error>ATENCION: Faltan algunos archivos dentro del esquema actual.</error>'. PHP_EOL );
			return;
		}

		// Crea el directorio para la version del esquema.
		$this->mkdir( $path_schema.$version );

		foreach( $files_schema as $file )
		{
			$fs->copy( $file, $path_schema.$version.'/'.basename( str_replace('\\', '/', $file) ), true);
		}

		$output->write( " <info>Se ha congelado correctamente su esquema esctual a la version $version.</info>" . PHP_EOL );
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