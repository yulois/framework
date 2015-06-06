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
use Symfony\Component\Console\Question\Question;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Finder\Finder;

Class ModelCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('app:models')
			->setDescription('Crea las clases del Modelo en el Bundle especificado como argumento.')
			->addArgument(
				'namespace',
				InputArgument::REQUIRED,
				'namespace of bundle'
			)
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Version a utilizar'
            );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $helper = $this->getHelper('question');
		$bundle = $input->getArgument('namespace');
		$version = $input->getArgument('version');
        $yaml = new Parser();

		$bundle = trim($bundle, '/');
		$bundle = trim($bundle, '\\');

        $path_schema = YS_APP . 'storage/schemas/';

        if($version === null)
        {
            $question = new Question(PHP_EOL .' Por favor, ingrese la version del esquema que desea utilizar [current]: ', 'current');
            $version = $helper->ask( $input, $output, $question );
        }

        if($version != 'current')
        {
			while( !preg_match('/^([1-9][0-9\.]+[0-9])+$/', $version) )
			{
				$output->writeln( PHP_EOL .' <error>ATENCION: La version no tiene un formato valido, debe ingresar por ejemplo: 1.0</error>' );

                $question = new Question(PHP_EOL .' Por favor, ingrese la version del esquema que desea utilizar [current]: ', 'current');
                $version = $helper->ask( $input, $output, $question );

                if( $version == 'current' )
                {
                    break;
                }
			}
		}

		if( !is_dir( $path_schema . $version ) )
		{
			$output->writeln( PHP_EOL . " <error>No se encontro el esquema dentro del directorio {$version}/ del bundle {$bundle}</error>" . PHP_EOL );
			exit;
		}

		// Obtiene el contenido del archivo *.yml
		$schema = array();

        $finder = new Finder();
        $finder->files()->name('*.yml')->in(str_replace('\\', '/',$path_schema . $version . '/bundles/'.$bundle));

        // Une todos los esquemas en un solo array
        foreach( $finder as $file )
        {
            // Concatena el esquema de cada archivo conseguido
            $schema = array_merge($schema, $yaml->parse(file_get_contents($file)));
        }

		$this->generateModels( $input, $output, $schema, $bundle );
	}

	public function generateModels( $input, $output, $schema, $bundle )
	{
		$GenerateClass = \AppKernel::get( 'generate_class' );
        $prefix = \AppKernel::get('config')->get('db', 'prefix', '');

		$output->write( PHP_EOL . "Lista de clases para el modelo:" . PHP_EOL );

		$this->mkdir( YS_BUNDLES . $bundle . '/Models/Base/' );

		foreach ( $schema as $table => $options )
		{
			if( !is_file(YS_BUNDLES . $bundle . '/Models/' . ucfirst( $table ).'.php') )
			{
				$GenerateClass->setTemplate( 'Model' );
				$GenerateClass->setNameClass( $table );
                $GenerateClass->setValues(array('_prefix'=>$prefix));
				$GenerateClass->setNamespace( ucfirst( str_replace('/', '\\', $bundle) ) . '\Models' );
				$GenerateClass->setNameClassExtend( 'Base\\'.ucfirst($table).'ModelBase' );
				$GenerateClass->create( YS_BUNDLES . $bundle . '/Models/' . ucfirst( $table ), $options );

				$output->write( " - Clase Model $table del Bundle $bundle, fue creada correctamente." . PHP_EOL );
			}

			$options['package'] = $bundle;
			$GenerateClass->setTemplate( 'BaseModel' );
			$GenerateClass->setNameClass( ucfirst($table).'ModelBase' );
			$GenerateClass->setNamespace( ucfirst( str_replace('/', '\\', $bundle) ) . '\Models\Base' );
			$GenerateClass->create( YS_BUNDLES . $bundle . '/Models/Base/' . ucfirst( $table ).'ModelBase', $options );

			$output->write( " - Clase Model Base$table del Bundle $bundle, fue creada correctamente." . PHP_EOL );
		}

		$output->write( PHP_EOL . PHP_EOL );
	}

	private function mkdir($path)
	{
		$fs = new Filesystem();

		try
		{
			$fs->mkdir($path);

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
		echo "

";
		foreach ( $errors as $error )
		{
			echo $error . "\n";
		}
		echo "

";
	}
}