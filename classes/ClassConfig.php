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
use ClassConfig\Exceptions\ClassConfigAlreadyRegisteredException;
use ClassConfig\Exceptions\ClassConfigNotRegisteredException;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Class ClassConfig
 * @package ClassConfig
 */
class ClassConfig
{
    /**
     * Config files are always re-generated when requested.
     */
    const CACHE_NEVER       = 0;

    /**
     * Config files are re-generated if older than the source (filemtime).
     */
    const CACHE_VALIDATE    = 1;

    /**
     * Config files are only generated once (or after being manually deleted).
     */
    const CACHE_ALWAYS      = 2;

    /**
     * Flag to determine whether the register() method has been called.
     *
     * @var bool
     */
    protected static $registered = false;

    /**
     * In-memory cache for the annotation reader.
     *
     * @var AnnotationReader
     */
    protected static $annotationReader;

    /**
     * The registered path to a cache folder.
     *
     * @var string
     */
    protected static $cachePath;

    /**
     * The registered caching strategy.
     *
     * @var int
     */
    protected static $cacheStrategy;

    /**
     * The registered class namespace for config classes.
     * This will be used as prefix to source classes.
     *
     * @var string
     */
    protected static $classNamespace;

    /**
     * A classmap of generated config files for the autoloader.
     *
     * @var string[]
     */
    protected static $classmap;

    /**
     * A flag to determine that the classmap is dirty and should be written to the filesystem.
     *
     * @var bool
     */
    protected static $classmapDirty = false;

    /**
     * @param string $path
     */
    protected static function createDirectories(string $path)
    {
        if (!is_dir($path)) {
            static::createDirectories(dirname($path));
            mkdir($path);
        }
    }

    /**
     * Lazy getter for the annotation reader.
     *
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
     * Getter for the registered cache path.
     * Throws a ClassConfigNotRegisteredException if register() wasn't called prior.
     *
     * @return string
     * @throws ClassConfigNotRegisteredException
     */
    protected static function getCachePath(): string
    {
        if (!static::$registered) {
            throw new ClassConfigNotRegisteredException();
        }
        return static::$cachePath;
    }

    /**
     * Getter for the registered class namespace.
     * Throws a ClassConfigNotRegisteredException if register() wasn't called prior.
     *
     * @return string
     * @throws ClassConfigNotRegisteredException
     */
    protected static function getClassNamespace(): string
    {
        if (!static::$registered) {
            throw new ClassConfigNotRegisteredException();
        }
        return self::$classNamespace;
    }

    /**
     * @param Config $annotation
     * @param string $className
     * @param string $classNamespace
     * @param string $targetClassNamespace
     * @param string $targetCanonicalClassName
     * @param int $time
     * @param int $subClassIteration
     * @return string
     */
    protected static function generate(
        Config $annotation,
        string $className,
        string $classNamespace,
        string $targetClassNamespace,
        string $targetCanonicalClassName,
        int $time,
        int &$subClassIteration = 0
    ): string {
        // a suffix of _0, _1, _2 etc. is added to generated sub-classes
        $suffix = 0 < $subClassIteration ? '_' . $subClassIteration : '';

        $effectiveClassName = $className . $suffix;
        $effectiveTargetCanonicalClassName = $targetCanonicalClassName . $suffix;

        $generator = new ClassGenerator($annotation, $effectiveClassName, $targetClassNamespace);

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
                    $type = $entry->getType();
                    $generator
                        ->generateProperty($key, $type, isset($entry->default) ? $entry->default : null)
                        ->generateGet($key, $type)
                        ->generateSet($key, $type)
                        ->generateIsset($key)
                        ->generateUnset($key);
                    break;

                case $entry instanceof ConfigArray:
                    $type = $entry->value->getType();
                    $generator
                        ->generateProperty($key, $type . '[]')
                        ->generateArrayGet($key, $type . '[]')
                        ->generateArraySet($key, $type . '[]')
                        ->generateArrayGetAt($key, $type)
                        ->generateArraySetAt($key, $type)
                        ->generateArrayClear($key)
                        ->generateArrayPush($key, $type)
                        ->generateArrayUnshift($key, $type)
                        ->generateArrayPop($key, $type)
                        ->generateArrayShift($key, $type)
                        ->generateIsset($key)
                        ->generateUnset($key);
                    break;

                case $entry instanceof Config:
                    $subClassIteration++;
                    $entryCanonicalClassName = static::generate(
                        $entry,
                        $className,
                        $classNamespace,
                        $targetClassNamespace,
                        $targetCanonicalClassName,
                        $time,
                        $subClassIteration
                    );
                    $generator
                        ->generateProperty($key, $entryCanonicalClassName)
                        ->generateConfigGet($key, $entryCanonicalClassName)
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

        $targetPath = static::getCachePath() . '/CC__' . str_replace('\\', '_', $classNamespace) . '_' .
            $effectiveClassName . '.php';

        file_put_contents($targetPath, (string) $generator);
        touch($targetPath, $time);
        clearstatcache();

        static::$classmapDirty = true;
        static::$classmap[$effectiveTargetCanonicalClassName] = $targetPath;

        return $effectiveTargetCanonicalClassName;
    }

    /**
     * Register the environment.
     * This must be called once and only once (on each request) before working with the library.
     *
     * @param string $cachePath
     * @param int $cacheStrategy
     * @param string $classNamespace
     */
    public static function register(
        string $cachePath,
        int $cacheStrategy = self::CACHE_VALIDATE,
        string $classNamespace = 'ClassConfig\Cache'
    ) {
        if (static::$registered) {
            throw new ClassConfigAlreadyRegisteredException();
        }

        // ensure the cache folder exists
        static::createDirectories($cachePath);

        static::$registered = true;
        static::$cachePath = $cachePath;
        static::$cacheStrategy = $cacheStrategy;
        static::$classNamespace = $classNamespace;

        // load the classmap
        $classmapPath = $cachePath . '/classmap.php';
        if (static::CACHE_NEVER === $cacheStrategy) {
            static::$classmap = [];
        } else {
            static::$classmap = is_file($classmapPath) ? include $classmapPath : [];
        }

        // flush the classmap if dirty on shutdown
        register_shutdown_function(function () use ($classmapPath) {
            if (static::$classmapDirty) {
                file_put_contents(
                    $classmapPath,
                    '<?php' . PHP_EOL . PHP_EOL .
                    '/**' . PHP_EOL .
                    ' * THIS IS AN AUTOMATICALLY GENERATED FILE, PLEASE DO NOT MODIFY IT.' . PHP_EOL .
                    ' * YOU MAY SAFELY DELETE THE FILE AS IT WILL BE REGENERATED ON-DEMAND.' . PHP_EOL .
                    ' */' . PHP_EOL .
                    'return ' . var_export(static::$classmap, true) . ';'
                );
            }
        });

        // autoload classes from the classmap
        spl_autoload_register(function (string $class) {
            if (isset(static::$classmap[$class])) {
                include static::$classmap[$class];
            }
        });
    }

    /**
     * @param string $canonicalClassName
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws ClassConfigNotRegisteredException
     */
    public static function createClass(string $canonicalClassName): string
    {
        $parts = explode('\\', $canonicalClassName);

        $className = $parts[count($parts) - 1];
        $classNamespace = implode('\\', array_slice($parts, 0, -1));

        $targetClassNamespace = static::getClassNamespace() . '\\' . $classNamespace;
        $targetCanonicalClassName = $targetClassNamespace . '\\' . $className;

        switch (static::$cacheStrategy) {
            case static::CACHE_NEVER:
                // always regenerate
                $time = time();
                break;

            case static::CACHE_ALWAYS:
                // only generate if not in the classmap
                if (isset(static::$classmap[$targetCanonicalClassName])) {
                    return $targetCanonicalClassName;
                }

                $time = time();
                break;

            case static::CACHE_VALIDATE:
            default:
                // validate by last modified time
                $time = filemtime((new \ReflectionClass($canonicalClassName))->getFileName());

                if (
                    isset(static::$classmap[$targetCanonicalClassName]) &&
                    @filemtime(static::$classmap[$targetCanonicalClassName]) === $time
                ) {
                    return $targetCanonicalClassName;
                }
                break;
        }

        /** @var Config $annotation */
        $annotation = static::getAnnotationReader()->getClassAnnotation(
            new \ReflectionClass($canonicalClassName),
            Config::class
        );

        return static::generate(
            $annotation,
            $className,
            $classNamespace,
            $targetClassNamespace,
            $targetCanonicalClassName,
            $time
        );
    }

    /**
     * @param string $class
     * @return AbstractConfig
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws ClassConfigNotRegisteredException
     */
    public static function createInstance(string $class): AbstractConfig
    {
        $canonicalClassName = static::createClass($class);
        return new $canonicalClassName;
    }
}