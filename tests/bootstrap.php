<?php

/** @var Composer\Autoload\ClassLoader $loader */

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = include __DIR__ . '/../vendor/autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);