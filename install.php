<?php

require __DIR__ . '/vendor/autoload.php';

function deleteFilesInDir($path_to_dir): void
{
    if (!is_dir($path_to_dir)) {
        return;
    }

    $tree_files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(
            $path_to_dir,
            \FilesystemIterator::SKIP_DOTS
        ),
        \RecursiveIteratorIterator::CHILD_FIRST
    );


    foreach ($tree_files as $file) {
        if ($file->isFile()) {
            \unlink($file->getPathname());
        }
        if ($file->isDir()) {
            \rmdir($file->getPathname());
        }
    }

    clearstatcache(true, $path_to_dir);
}

deleteFilesInDir('src/Schema');
deleteFilesInDir('resources/jms');

shell_exec('vendor/bin/xsd2php convert config/xsd.yml resources/schema/*.xsd');
shell_exec('vendor/bin/soap-server generate config/server.yml --dest-class=Schema/Server/SoapServerContainer src/Schema/Server');

//shell_exec('vendor/bin/soap-client generate config/client.yml --dest-class=Schema/Client/SoapClientContainer src/Schema/Client');
