<?php

use \Schoenfinkel\Integration\CodeGenerator;
use \Schoenfinkel\Integration\PostfixClassNameMapping;
use \Schoenfinkel\Integration\ClassLoader\SchoenfinkelClassLoader;

$loader = require __DIR__ . '/../vendor/autoload.php';
$custom = new SchoenfinkelClassLoader($loader, new CodeGenerator(new PostfixClassNameMapping()));
$loader->unregister();
spl_autoload_register(array($custom, 'loadClass'));