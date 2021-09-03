<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
spl_autoload_register("load");

function load($classname) {
    $file_path = 'src/' . str_replace("\\", "/", $classname) . ".php";
    $file_exists = file_exists($file_path);
    if ($file_exists) {
        include $file_path;
    }
    return $file_exists;
}

use GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\BaseTypesHandler;
use GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\XmlSchemaDateHandler;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializerBuilder;
use Schema\Xsd\Test\TestRequest as XsdTestRequest;
use Schema\Server\Test\TestRequest as ServerTestRequest;
use Schema\Client\Test\TestRequest as ClientTestRequest;

$serializerBuilder = SerializerBuilder::create();
$serializerBuilder->addMetadataDir('resources/jms/client', 'Schema\Client\Test');
$serializerBuilder->configureHandlers(function (HandlerRegistryInterface $handler) use ($serializerBuilder) {
    $serializerBuilder->addDefaultHandlers();
    $handler->registerSubscribingHandler(new BaseTypesHandler());
    $handler->registerSubscribingHandler(new XmlSchemaDateHandler());
});

$serializer = $serializerBuilder->build();

$xml = file_get_contents('resources/xml/xsd.xml');
$object = $serializer->deserialize($xml, ClientTestRequest::class, 'xml');
$new_xml = $serializer->serialize($object, 'xml');
