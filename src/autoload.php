<?php

spl_autoload_register(function($className) {
    $prefix = "Stormmore\Queries";
    if (str_starts_with($className, $prefix)) {
        $filename = str_replace($prefix, "", $className);
        $filename = str_replace("\\", "/", $filename);
        $filename .= ".php";

        $filepath = __DIR__ . $filename;
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
});
