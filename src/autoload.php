<?php

spl_autoload_register(function ($class) {
    $vendor = 'Straw\\';
    if (strpos($class, $vendor) !== 0) {
        return;
    }
    $replace = array($vendor => '/', '\\' => '/');
    require __DIR__ . strtr($class, $replace) . '.php';
});

