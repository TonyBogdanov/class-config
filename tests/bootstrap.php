<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

global $testLoader;
$testLoader = include __DIR__ . '/../vendor/autoload.php';

AnnotationRegistry::registerLoader([$testLoader, 'loadClass']);