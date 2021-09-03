<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

error_reporting(error_reporting() & ~E_NOTICE);

use GoetasWebservices\SoapServices\SoapClient\Command\Generate;
use GoetasWebservices\Xsd\XsdToPhp\Command\Convert;
use GoetasWebservices\Xsd\XsdToPhp\DependencyInjection\Xsd2PhpExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;

function deleteFilesInDir($path_to_dir): void
{
    if (!is_dir($path_to_dir)) {
        return;
    }

    $tree_files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $path_to_dir,
            FilesystemIterator::SKIP_DOTS
        ),
        RecursiveIteratorIterator::CHILD_FIRST
    );


    foreach ($tree_files as $file) {
        if ($file->isFile()) {
            unlink($file->getPathname());
        }
        if ($file->isDir()) {
            rmdir($file->getPathname());
        }
    }

    clearstatcache(true, $path_to_dir);
}

deleteFilesInDir('src/Schema');
deleteFilesInDir('resources/jms');

//shell_exec('vendor/bin/xsd2php convert config/xsd.yml resources/schema/*.xsd');
$input = new ArrayInput([
    'command' => 'convert',
    'config' => 'config/xsd.yml',
    'src' => glob('resources/schema/*.xsd'),
]);
$output = new ConsoleOutput();

$container = new ContainerBuilder();
$container->registerExtension(new Xsd2PhpExtension());
$container->set('logger', new ConsoleLogger($output));

$cli = new Application('Convert XSD to PHP classes Command Line Interface', '2.0');
$cli->setAutoExit(false);
$cli->setCatchExceptions(true);
$cli->add(new Convert($container));
$cli->run($input, $output);

//shell_exec('vendor/bin/soap-server generate config/server.yml --dest-class=Schema/Server/SoapContainer src/Schema/Server');
$input = new ArrayInput([
    'command' => 'generate',
    'config' => 'config/server.yml',
    'dest-dir' => 'src/Schema/Server',
]);
$cli = new Application('Convert WSDL definitions to PHP classes Command Line Interface', '1.0');
$cli->setAutoExit(false);
$cli->setCatchExceptions(true);
$cli->add(new \GoetasWebservices\SoapServices\SoapServer\Command\Generate());
$cli->run($input);

//shell_exec('vendor/bin/soap-client generate config/client.yml --dest-class=Schema/Client/SoapContainer src/Schema/Client');
$input = new ArrayInput([
    'command' => 'generate',
    'config' => 'config/client.yml',
    'dest-dir' => 'src/Schema/Client',
]);
$cli = new Application('Convert WSDL definitions to PHP classes Command Line Interface', '1.0');
$cli->setAutoExit(false);
$cli->setCatchExceptions(true);
$cli->add(new Generate());
$cli->run($input);
