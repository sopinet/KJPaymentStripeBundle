<?php

$autoloader = __DIR__.'/../vendor/autoload.php';

if (!file_exists($autoloader)) {
    throw new RuntimeException("Looks like you're missing Symfony. Run composer before running tests!");
}
$autoload = require_once $autoloader;