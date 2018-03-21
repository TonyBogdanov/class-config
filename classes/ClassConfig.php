<?php

namespace ClassConfig;

use ClassConfig\Annotation\Config;
use ClassConfig\Annotation\ConfigArray;
use ClassConfig\Annotation\ConfigBoolean;
use ClassConfig\Annotation\ConfigEntryInterface;
use ClassConfig\Annotation\ConfigFloat;
use ClassConfig\Annotation\ConfigInteger;
use ClassConfig\Annotation\ConfigObject;
use ClassConfig\Annotation\ConfigString;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Class ClassConfig
 * @package ClassConfig
 */
class ClassConfig
{
    /**
     * @var AnnotationReader
     */
    protected static $annotationReader;

    /**
     * @var string
     */
    protected static $cachePath;

    /**
     * @var string
     */
    protected static $classNamespace = 'ClassConfig\Cache';

    /**
     * @return AnnotationReader
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    protected static function getAnnotationReader(): AnnotationReader
    {
        if (!isset(static::$annotationReader)) {
            static::$annotationReader = new AnnotationReader();
        }
        return static::$annotationReader;
    }

    /**
     * @param Config $annotation
     * @param string $className
     * @param string $classNamespace
     * @param int $subClassIteration
     * @return string
     */
    protected static function generate(
        Config $annotation,
        string $className,
        string $classNamespace,
        int &$subClassIteration = 0
    ): string {
        $canonicalClassName = $classNamespace . '\\' . $className;

        $effectiveClassName = $className . (0 < $subClassIteration ? '_' . $subClassIteration : '');
        $effectiveCanonicalClassName = $canonicalClassName . (0 < $subClassIteration ? '_' . $subClassIteration : '');

        $generator = new ClassGenerator($annotation, $effectiveClassName, $classNamespace);

        /**
         * @var string $key
         * @var ConfigEntryInterface $entry
         */
        foreach ($annotation->value as $key => $entry) {
            switch (true) {
                case $entry instanceof ConfigString:
                case $entry instanceof ConfigInteger:
                case $entry instanceof ConfigFloat:
                case $entry instanceof ConfigBoolean:
                case $entry instanceof ConfigObject:
                    $generator
                        ->generateProperty($key, $entry->getType(), isset($entry->default) ? $entry->default : null)
                        ->generateGet($key, $entry->getType())
                        ->generateSet($key, $entry->getType())
                        ->generateIsset($key)
                        ->generateUnset($key);
                    break;

                case $entry instanceof ConfigArray:
                    $generator
                        ->generateProperty($key, $entry->value->getType() . '[]')
                        ->generateArrayGet($key, $entry->value->getType() . '[]')
                        ->generateArraySet($key, $entry->value->getType() . '[]')
                        ->generateArrayGetAt($key, $entry->value->getType())
                        ->generateArraySetAt($key, $entry->value->getType())
                        ->generateArrayClear($key)
                        ->generateArrayPush($key, $entry->value->getType())
                        ->generateArrayUnshift($key, $entry->value->getType())
                        ->generateArrayPop($key, $entry->value->getType())
                        ->generateArrayShift($key, $entry->value->getType())
                        ->generateIsset($key)
                        ->generateUnset($key);
                    break;

                case $entry instanceof Config:
                    $subClassIteration++;
                    $entryCanonicalClassName = static::generate(
                        $entry,
                        $className,
                        $classNamespace,
                        $subClassIteration
                    );
                    $generator
                        ->generateProperty($key, $entryCanonicalClassName)
                        ->generateGet($key, $entryCanonicalClassName)
                        ->generateConfigSet($key)
                        ->generateConfigIsset($key)
                        ->generateConfigUnset($key);
                    break;

                default:
                    throw new \RuntimeException(sprintf(
                        'Invalid or unsupported configuration entry type: "%s".',
                        get_class($entry)
                    ));
            }
        }

        $generator
            ->generateMagicGet()
            ->generateMagicSet()
            ->generateMagicIsset()
            ->generateMagicUnset();

        file_put_contents(
            static::getCachePath() . '/' . $effectiveClassName . '.php',
            (string) $generator
        );

        return $effectiveCanonicalClassName;
    }

    /**
     * @param string $path
     */
    public static function setCachePath(string $path)
    {
        static::$cachePath = $path;
    }

    /**
     * @return string
     */
    public static function getCachePath(): string
    {
        return static::$cachePath ?: sys_get_temp_dir();
    }

    /**
     * @return string
     */
    public static function getClassNamespace(): string
    {
        return self::$classNamespace;
    }

    /**
     * @param string $classNamespace
     */
    public static function setClassNamespace(string $classNamespace)
    {
        static::$classNamespace = $classNamespace;
    }

    /**
     * @param string $class
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function createClass(string $class): string
    {
        $class = new \ReflectionClass($class);

        $className = 'CC__' . hash('crc32', $class->getName() . ':' . filemtime($class->getFileName()));;
        $classNamespace = static::getClassNamespace();
        $canonicalClassName = $classNamespace . '\\' . $className;

        $path = static::getCachePath() . DIRECTORY_SEPARATOR . $className . '.php';
        if (is_file($path)) {
            return $canonicalClassName;
        }

        /** @var Config $annotation */
        $annotation = static::getAnnotationReader()->getClassAnnotation($class, Config::class);
        static::generate($annotation, $className, $classNamespace);

        return $canonicalClassName;
    }

    /**
     * @param string $class
     * @return AbstractConfig
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function createInstance(string $class): AbstractConfig
    {
        $canonicalClassName = static::createClass($class);
        return new $canonicalClassName;
    }
}