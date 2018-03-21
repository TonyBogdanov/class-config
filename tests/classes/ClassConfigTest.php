<?php

namespace ClassConfig\Test;

use ClassConfig\AbstractConfig;
use ClassConfig\ClassConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class ClassConfigTest
 *
 * @coversDefaultClass \ClassConfig\ClassConfig
 *
 * @package ClassConfig\Test
 */
class ClassConfigTest extends TestCase
{
    const SAMPLES = 50;

    const SAMPLE_NAMESPACE = 'ClassConfig\Test';
    const SAMPLE_SHORT_NAME = 'Sample';

    const SAMPLE_TARGET_NAMESPACE = 'ClassConfig\Test\Sample';
    const SAMPLE_TARGET_SHORT_NAME = 'Sample';

    /**
     * @return array
     */
    protected function prepareCaches(): array
    {
        $cache = __DIR__ . '/../../cache';
        if (!is_dir($cache)) {
            mkdir($cache);
        }

        $cacheSamples = $cache . '/samples';
        if (!is_dir($cacheSamples)) {
            mkdir($cacheSamples);
        }

        $cacheConfigs = $cache . '/configs';
        if (!is_dir($cacheConfigs)) {
            mkdir($cacheConfigs);
        }

        return [$cacheSamples, $cacheConfigs];
    }

    /**
     * @param string $cache
     * @return ClassConfigTest
     */
    protected function flushCache(string $cache): ClassConfigTest
    {
        foreach (glob($cache . '/*') as $path) {
            unlink($path);
        }
        return $this;
    }

    /**
     * @param int $samples
     * @param string $cache
     * @return ClassConfigTest
     * @throws \ReflectionException
     */
    protected function generateSamples(int $samples, string $cache): ClassConfigTest
    {
        for ($i = 0; $i < $samples; $i++) {
            file_put_contents(
                $cache . '/' . static::SAMPLE_TARGET_SHORT_NAME . $i . '.php',
                preg_replace(
                    '/^class ' . static::SAMPLE_SHORT_NAME . '/mi',
                    'class ' . static::SAMPLE_TARGET_SHORT_NAME . $i,
                    preg_replace(
                        '/^namespace ' . preg_quote(static::SAMPLE_NAMESPACE, '/') . ';/mi',
                        'namespace ' . static::SAMPLE_TARGET_NAMESPACE . ';',
                        file_get_contents((new \ReflectionClass(Sample::class))->getFileName())
                    )
                )
            );
        }
        return $this;
    }

    /**
     * @covers ::createClass()
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function testCreateFresh()
    {
        list($cacheSamples, $cacheConfigs) = $this->prepareCaches();

        $this->flushCache($cacheSamples);
        $this->flushCache($cacheConfigs);

        $this->generateSamples(static::SAMPLES, $cacheSamples);

        ClassConfig::setCachePath($cacheConfigs);

        $classes = [];

        for ($i = 0; $i < static::SAMPLES; $i++) {
            $class = ClassConfig::createClass(static::SAMPLE_TARGET_NAMESPACE . '\\' .
                static::SAMPLE_TARGET_SHORT_NAME . $i);

            $this->assertNotContains($class, $classes, 'createClass() produces different class names for different' .
                ' samples.');
            $this->assertTrue(class_exists($class), 'createClass() produces names of classes that can be autoloaded.');

            $classes[] = $class;
        }
    }

    /**
     * @covers ::createInstance()
     * @depends testCreateFresh
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function testCreateCached()
    {
        $durationDirect = 0;
        $durationConfig = 0;

        for ($i = 0; $i < static::SAMPLES; $i++) {
            $sample = static::SAMPLE_TARGET_NAMESPACE . '\\' . static::SAMPLE_TARGET_SHORT_NAME . $i;
            $configClassName = ClassConfig::createClass($sample);

            $start = microtime(true);
            $object = new $configClassName;
            $durationDirect += microtime(true) - $start;

            $start = microtime(true);
            $config = ClassConfig::createInstance($sample);
            $durationConfig += microtime(true) - $start;

            $this->assertInstanceOf(AbstractConfig::class, $config, 'createInstance() produces instances of ' .
                AbstractConfig::class . '.');
            $this->assertSame(AbstractConfig::class, get_parent_class($config), 'createInstance() produces' .
                ' instances of classes which extend from ' . AbstractConfig::class . '.');
            $this->assertSame(get_class($object), get_class($config), 'createInstance() and direct instantiation' .
                ' both create an object of the same class.');
            $this->assertNotSame($object, $config, 'createInstance() and direct instantiation create different' .
                ' instances.');
        }

        $this->assertLessThan(0.05, $durationConfig / static::SAMPLES, 'createInstance() completes in less than' .
            ' 50ms (on average) for a single cached sample.');
        $this->assertLessThan(50, $durationConfig / $durationDirect, 'createInstance()\'s overhead is less than' .
            ' 50 times the duration of a direct instantiation.');
    }
}