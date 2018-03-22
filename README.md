# ClassConfig

[![ApiGen Docs](https://img.shields.io/badge/ApiGen-Docs-053368.svg)](https://tonybogdanov.github.io/class-config/docs)

[![Latest Stable Version](https://poser.pugx.org/tonybogdanov/class-config/v/stable?format=flat)](https://packagist.org/packages/tonybogdanov/class-config)
[![Latest Unstable Version](https://poser.pugx.org/tonybogdanov/class-config/v/unstable?format=flat)](https://packagist.org/packages/tonybogdanov/class-config)
[![Build Status](https://travis-ci.org/TonyBogdanov/class-config.svg?branch=master)](https://travis-ci.org/TonyBogdanov/class-config)
[![Coverage Status](https://coveralls.io/repos/github/TonyBogdanov/class-config/badge.svg?branch=master)](https://coveralls.io/github/TonyBogdanov/class-config?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TonyBogdanov/class-config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TonyBogdanov/class-config/?branch=master)

[![License](https://poser.pugx.org/tonybogdanov/class-config/license?format=flat)](https://packagist.org/packages/tonybogdanov/class-config)
[![Buy Me Coffee](https://img.shields.io/badge/buy_me-coffee-00cae9.svg)](http://ko-fi.co/1236KUKJNC96B)

Per-object configuration from class annotations in PHP.

## Installation

Install via [Composer](https://getcomposer.org):

```sh
composer require tonybogdanov/class-config
```

## Configuration

Configure the library by calling `ClassConfig::register()`, choosing a cache directory, cache strategy and optionally a config class namespace, once and only once prior to using any of the features.

Add an autoload PSR-4 entry in `composer.json` and point the `ClassConfig\Cache\` namespace (or the one you've configured) to the cache folder you chose.

Until [`doctrine/annotations`](https://github.com/doctrine/annotations) reaches version `2.0` you'll also need to manually register the composer autoloader with the annotations' registry:

```php
$loader = include 'vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
```

Alternatively you can try [`indigophp/doctrine-annotation-autoload`](https://github.com/indigophp/doctrine-annotation-autoload).

## Testing

Run the tests:

```sh
./bin/run-tests.sh
```

[Check the Coverage](https://tonybogdanov.github.io/class-config/coverage/).

## Usage

TODO

## API Docs

[Check the Docs](https://tonybogdanov.github.io/class-config/docs/)