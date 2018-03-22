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
     * @param string $path
     * @return ClassConfigTest
     */
    protected function flushCache(string $path): ClassConfigTest
    {
        $path = rtrim($path, '\\/') . '/';
        $open = opendir($path);

        while (false !== ($read = readdir($open))) {
            if ('.' === $read || '..' === $read) {
                continue;
            }
            if (is_file($path . $read)) {
                unlink($path . $read);
            } else if (is_dir($path . $read)) {
                static::flushCache($path . $read);
                rmdir($path . $read);
            }
        }

        closedir($open);
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
        clearstatcache();
        return $this;
    }

    /**
     * @param callable $warmup
     * @param string $cacheStrategyName
     * @param int $maxDurationMS
     * @param int $maxOverhead
     * @return ClassConfigTest
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    protected function doTestCreate(
        callable $warmup,
        string $cacheStrategyName,
        int $maxDurationMS,
        int $maxOverhead
    ): ClassConfigTest {
        list($cacheSamples, $cacheConfigs) = $this->prepareCaches();

        $this->flushCache($cacheSamples);
        $this->flushCache($cacheConfigs);
        $this->generateSamples(static::SAMPLES, $cacheSamples);

        call_user_func($warmup, $cacheConfigs);

        $durationDirect = 0;
        $durationInstance = 0;

        for ($i = 0; $i < static::SAMPLES; $i++) {
            $sample = static::SAMPLE_TARGET_NAMESPACE . '\\' . static::SAMPLE_TARGET_SHORT_NAME . $i;

            $start = microtime(true);
            $config = ClassConfig::createInstance($sample);
            $durationInstance += microtime(true) - $start;

            $configClassName = get_class($config);

            $start = microtime(true);
            $object = new $configClassName;
            $durationDirect += microtime(true) - $start;

            $this->assertInstanceOf(AbstractConfig::class, $config, 'createInstance() produces instances of ' .
                AbstractConfig::class . ' (cache=' . $cacheStrategyName . ').');
            $this->assertSame(AbstractConfig::class, get_parent_class($config), 'createInstance() produces' .
                ' instances of classes which extend from ' . AbstractConfig::class . ' (cache=' . $cacheStrategyName .
                ').');
            $this->assertSame(get_class($object), get_class($config), 'createInstance() and direct instantiation' .
                ' both create an object of the same class (cache=' . $cacheStrategyName . ').');
            $this->assertNotSame($object, $config, 'createInstance() and direct instantiation create different' .
                ' instances (cache=' . $cacheStrategyName . ').');
        }

        $this->assertLessThan($maxDurationMS / 1000, $durationInstance / static::SAMPLES,
            'createInstance() completes in less than ' . $maxDurationMS . 'ms (on average) for a single' .
            ' sample (cache=' . $cacheStrategyName . ').');
        $this->assertLessThan($maxOverhead, $durationInstance / $durationDirect,
            'createInstance()\'s overhead is less than ' . $maxOverhead . ' times the duration of a direct' .
            ' instantiation (cache=' . $cacheStrategyName . ').');

        $this->flushCache($cacheSamples);
        $this->flushCache($cacheConfigs);

        return $this;
    }

    /**
     * @covers ::createInstance()
     * @covers ::createClass()
     *
     * @runInSeparateProcess
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function testCreateCacheNever()
    {
        $this->doTestCreate(function (string $cacheConfigs) {
            ClassConfig::register($cacheConfigs, ClassConfig::CACHE_NEVER);
        }, 'never', 500, PHP_INT_MAX);
    }

    /**
     * @covers ::createInstance()
     * @covers ::createClass()
     *
     * @runInSeparateProcess
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function testCreateCacheValidate()
    {
        $this->doTestCreate(function (string $cacheConfigs) {
            ClassConfig::register($cacheConfigs, ClassConfig::CACHE_VALIDATE);

            for ($i = 0; $i < static::SAMPLES; $i++) {
                ClassConfig::createClass(static::SAMPLE_TARGET_NAMESPACE . '\\' . static::SAMPLE_TARGET_SHORT_NAME . $i);
            }
        }, 'validate', 20, 200);
    }

    /**
     * @covers ::createInstance()
     * @covers ::createClass()
     *
     * runInSeparateProcess
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function testCreateCacheAlways()
    {
        $this->doTestCreate(function (string $cacheConfigs) {
            ClassConfig::register($cacheConfigs, ClassConfig::CACHE_ALWAYS);

            for ($i = 0; $i < static::SAMPLES; $i++) {
                ClassConfig::createClass(static::SAMPLE_TARGET_NAMESPACE . '\\' . static::SAMPLE_TARGET_SHORT_NAME . $i);
            }
        }, 'always', 10, 100);
    }
}