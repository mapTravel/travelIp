<?php

ini_set('display_errors', 'on');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

spl_autoload_register(function ($class) {

    $path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    if (is_file($path)) {
        require $path;
        return;
    }

    throw new \LogicException(sprintf('Class "%s" not found in "%s"', $class, $path));
});

require __DIR__ . '/func.php';
require __DIR__ . '/AppController.php';