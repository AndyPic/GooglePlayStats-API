<?php

/*
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');
*/

spl_autoload_register(function ($class_name) {
    // Fix for namespaces
    $class_name = str_replace("\\", DIRECTORY_SEPARATOR, $class_name);
    include_once($_SERVER['DOCUMENT_ROOT'] . '/API/' . $class_name . '.php');
});