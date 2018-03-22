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

Until [`doctrine/annotations`](https://github.com/doctrine/annotations) reaches version `2.0` you'll need to manually register the composer autoloader with the annotations registry:

```php
$loader = include 'vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
```

You can also try [`indigophp/doctrine-annotation-autoload`](https://github.com/indigophp/doctrine-annotation-autoload) to do it automatically, but I couldn't make it work with Travis.

## Testing

Run the tests:

```sh
./bin/run-tests.sh
```

[Check the Coverage](https://tonybogdanov.github.io/class-config/coverage/).

## API Docs

[Check the Docs](https://tonybogdanov.github.io/class-config/docs/)