<?php

spl_autoload_register(function($class)
{
    $file = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, explode('\\', $class)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

\WebDAV\Stream\Wrapper::register();