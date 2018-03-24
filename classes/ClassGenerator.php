<?php

namespace ClassConfig;

use ClassConfig\Annotation\Config;
use Nette\InvalidArgumentException;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;

/**
 * Class ClassGenerator
 * @package ClassConfig
 */
class ClassGenerator
{
    /**
     * @var ClassType
     */
    protected $class;

    /**
     * @var string
     */
    protected $ownerCanonicalClassName;

    /**
     * @param string $value
     * @return string
     */
    protected static function camelCase(string $value): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getTypeHint(string $type)
    {
        if ('[]' === substr($type, -2)) {
            return 'array';
        }

        if ('mixed' === $type) {
            return '';
        }

        return $type;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getCommentTypeHint(string $type)
    {
        if (preg_match('/^(.+?)((?:\[\])+)$/', $type, $match)) {
            $type = $match[1];
            $brackets = $match[2];
        } else {
            $brackets = '';
        }

        if (!in_array($type, ['string', 'int', 'float', 'bool', 'mixed'], true)) {
            $this->class->getNamespace()->addUse($type);
            return $this->class->getNamespace()->unresolveName($type) . $brackets;
        }

        return $type . $brackets;
    }

    /**
     * @return string
     */
    protected function getCanonicalClassName(): string
    {
        return $this->class->getNamespace()->getName() . '\\' . $this->class->getName();
    }

    /**
     * ClassGenerator constructor.
     *
     * @param Config $annotation
     * @param string $className
     * @param string $classNamespace
     * @param string $ownerCanonicalClassName
     */
    public function __construct(
        Config $annotation,
        string $className,
        string $classNamespace,
        string $ownerCanonicalClassName
    ) {
        $this->class = (new PhpNamespace($classNamespace))->addClass($className);
        $this->ownerCanonicalClassName = $ownerCanonicalClassName;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $this->class
            ->getNamespace()
            ->addUse(AbstractConfig::class);

        $this->class
            ->setFinal(true)
            ->addExtend(AbstractConfig::class)
            ->addComment(
                'THIS IS AN AUTOMATICALLY GENERATED FILE, PLEASE DO NOT MODIFY IT.' . PHP_EOL .
                'YOU MAY SAFELY DELETE THE FILE AS IT WILL BE REGENERATED ON-DEMAND.'
            );

        $this->class
            ->addMethod('end')
            ->addComment(
                '@return ' . $this->getCommentTypeHint($this->ownerCanonicalClassName)
            )->setReturnType($this->getTypeHint($this->ownerCanonicalClassName))
            ->setBody(
                '/** @var ' . $this->getCommentTypeHint($this->ownerCanonicalClassName) . ' $owner */' . PHP_EOL .
                '$owner = $this->___owner;' . PHP_EOL .
                'return $owner;'
            );

        return '<?php' . PHP_EOL . PHP_EOL . (string) $this->class->getNamespace();
    }

    /**
     * @param string $name
     * @param string $type
     * @param null $default
     * @return ClassGenerator
     */
    public function generateProperty(string $name, string $type, $default = null): ClassGenerator
    {
        $this->class
            ->addProperty('__' . $name . '__', [null, $default])
            ->addComment(
                '@var ' . $this->getCommentTypeHint($type . '[]')
            )->setVisibility('private');
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateGet(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('get_' . $name))
            ->addComment(
                '@return null|' . $this->getCommentTypeHint($type)
            )->setReturnType($this->getTypeHint($type))
            ->setReturnNullable(true)
            ->setBody(
                'if (isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    return $this->__' . $name . '__[0];' . PHP_EOL .
                '}' . PHP_EOL .
                'return $this->__' . $name . '__[1];'
            );
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateSet(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('set_' . $name))
            ->addComment(
                '@param ' . $this->getCommentTypeHint($type) . ' $value' . PHP_EOL .
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                '$this->__' . $name . '__[0] = $value;' . PHP_EOL .
                'return $this;'
            )->addParameter('value')
            ->setTypeHint($this->getTypeHint($type));
        return $this;
    }

    /**
     * @param string $name
     * @return ClassGenerator
     */
    public function generateIsset(string $name): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('isset_' . $name))
            ->addComment(
                '@return bool'
            )->setReturnType('bool')
            ->setBody(
                'return isset($this->__' . $name . '__[0]);'
            );
        return $this;
    }

    /**
     * @param string $name
     * @return ClassGenerator
     */
    public function generateUnset(string $name): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('unset_' . $name))
            ->addComment(
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                'unset($this->__' . $name . '__[0]);' . PHP_EOL .
                'return $this;'
            );
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateListSet(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('set_' . $name))
            ->addComment(
                '@param ' . $this->getCommentTypeHint($type) . ' $values' . PHP_EOL .
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                '$this->' . static::camelCase('clear_' . $name) . '();' . PHP_EOL .
                'foreach ($values as $value) {' . PHP_EOL .
                '    $this->' . static::camelCase('push_' . $name) . '($value);' . PHP_EOL .
                '}' . PHP_EOL .
                'return $this;'
            )->addParameter('values')
            ->setTypeHint($this->getTypeHint($type));
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateListGetAt(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('get_' . $name . '_at'))
            ->addComment(
                '@param int $index' . PHP_EOL .
                '@return ' . $this->getCommentTypeHint($type)
            )->setReturnType($this->getTypeHint($type))
            ->setReturnNullable(true)
            ->setBody(
                'if (isset($this->__' . $name . '__[0]) && array_key_exists($index, $this->__' . $name . '__[0])) {' .
                PHP_EOL . '    return $this->__' . $name . '__[0][$index];' . PHP_EOL .
                '}' . PHP_EOL .
                'return null;'
            )->addParameter('index')
            ->setTypeHint('int');
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateListSetAt(string $name, string $type): ClassGenerator
    {
        $method = $this->class
            ->addMethod(static::camelCase('set_' . $name . '_at'))
            ->addComment(
                '@param int $index' . PHP_EOL .
                '@param ' . $this->getCommentTypeHint($type) . ' $value' . PHP_EOL .
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                'if (0 > $index || (0 < $index && (!isset($this->__' . $name . '__[0]) ||' . PHP_EOL .
                '    empty($this->__' . $name . '__[0])) || $index > count($this->__' . $name . '__[0]))) {' . PHP_EOL .
                '    return $this;' . PHP_EOL .
                '}' . PHP_EOL . PHP_EOL .
                'if (!isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    $this->__' . $name . '__[0] = [];' . PHP_EOL .
                '}' . PHP_EOL . PHP_EOL .
                '$this->__' . $name . '__[0][$index] = $value;' . PHP_EOL .
                'return $this;'
            );

        $method->addParameter('index')->setTypeHint('int');
        $method->addParameter('value')->setTypeHint($this->getTypeHint($type));

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateListPush(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('push_' . $name))
            ->addComment(
                '@param ' . $this->getCommentTypeHint($type) . ' $value' . PHP_EOL .
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                'if (!isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    $this->__' . $name . '__[0] = [];' . PHP_EOL .
                '}' . PHP_EOL .
                'array_push($this->__' . $name . '__[0], $value);' . PHP_EOL .
                'return $this;'
            )->addParameter('value')
            ->setTypeHint($this->getTypeHint($type));
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateListUnshift(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('unshift_' . $name))
            ->addComment(
                '@param ' . $this->getCommentTypeHint($type) . ' $value' . PHP_EOL .
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                'if (!isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    $this->__' . $name . '__[0] = [];' . PHP_EOL .
                '}' . PHP_EOL .
                'array_unshift($this->__' . $name . '__[0], $value);' . PHP_EOL .
                'return $this;'
            )->addParameter('value')
            ->setTypeHint($this->getTypeHint($type));
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateMapSet(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('set_' . $name))
            ->addComment(
                '@param ' . $this->getCommentTypeHint($type) . ' $values' . PHP_EOL .
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                '$this->' . static::camelCase('clear_' . $name) . '();' . PHP_EOL .
                'foreach ($values as $key => $value) {' . PHP_EOL .
                '    $this->' . static::camelCase('set_' . $name . '_at') . '($key, $value);' . PHP_EOL .
                '}' . PHP_EOL .
                'return $this;'
            )->addParameter('values')
            ->setTypeHint($this->getTypeHint($type));
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateMapGetAt(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('get_' . $name . '_at'))
            ->addComment(
                '@param mixed $key' . PHP_EOL .
                '@return ' . $this->getCommentTypeHint($type)
            )->setReturnType($this->getTypeHint($type))
            ->setReturnNullable(true)
            ->setBody(
                'if (isset($this->__' . $name . '__[0]) && array_key_exists($key, $this->__' . $name . '__[0])) {' .
                PHP_EOL . '    return $this->__' . $name . '__[0][$key];' . PHP_EOL .
                '}' . PHP_EOL .
                'return null;'
            )->addParameter('key');
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateMapSetAt(string $name, string $type): ClassGenerator
    {
        $method = $this->class
            ->addMethod(static::camelCase('set_' . $name . '_at'))
            ->addComment(
                '@param mixed $key' . PHP_EOL .
                '@param ' . $this->getCommentTypeHint($type) . ' $value' . PHP_EOL .
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                'if (!isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    $this->__' . $name . '__[0] = [];' . PHP_EOL .
                '}' . PHP_EOL . PHP_EOL .
                '$this->__' . $name . '__[0][$key] = $value;' . PHP_EOL .
                'return $this;'
            );

        $method->addParameter('key');
        $method->addParameter('value')->setTypeHint($this->getTypeHint($type));

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateArrayPop(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('pop_' . $name))
            ->addComment(
                '@return null|' . $this->getCommentTypeHint($type)
            )->setReturnType($this->getTypeHint($type))
            ->setReturnNullable(true)
            ->setBody(
                'if (!isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    return null;' . PHP_EOL .
                '}' . PHP_EOL .
                'return array_pop($this->__' . $name . '__[0]);'
            );
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateArrayShift(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('shift_' . $name))
            ->addComment(
                '@return null|' . $this->getCommentTypeHint($type)
            )->setReturnType($this->getTypeHint($type))
            ->setReturnNullable(true)
            ->setBody(
                'if (!isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    return null;' . PHP_EOL .
                '}' . PHP_EOL .
                'return array_shift($this->__' . $name . '__[0]);'
            );
        return $this;
    }

    /**
     * @param string $name
     * @return ClassGenerator
     */
    public function generateArrayClear(string $name): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('clear_' . $name))
            ->addComment(
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                'unset($this->__' . $name . '__[0]);' . PHP_EOL .
                'return $this;'
            );
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ClassGenerator
     */
    public function generateConfigGet(string $name, string $type): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('get_' . $name))
            ->addComment(
                '@return null|' . $this->getCommentTypeHint($type)
            )->setReturnType($this->getTypeHint($type))
            ->setBody(
                'if (!isset($this->__' . $name . '__[0])) {' . PHP_EOL .
                '    $this->__' . $name . '__[0] = new ' . $this->getCommentTypeHint($type) .
                '($this->___owner, $this, \'' . $name . '\');' . PHP_EOL .
                '}' . PHP_EOL .
                'return $this->__' . $name . '__[0];'
            );
        return $this;
    }

    /**
     * @param string $name
     * @return ClassGenerator
     */
    public function generateConfigSet(string $name): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('set_' . $name))
            ->addComment(
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                '// config is immutable' . PHP_EOL .
                'return $this;'
            );
        return $this;
    }

    /**
     * @param string $name
     * @return ClassGenerator
     */
    public function generateConfigIsset(string $name): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('isset_' . $name))
            ->addComment(
                '@return bool'
            )->setReturnType('bool')
            ->setBody(
                '// config is immutable' . PHP_EOL .
                'return true;'
            );
        return $this;
    }

    /**
     * @param string $name
     * @return ClassGenerator
     */
    public function generateConfigUnset(string $name): ClassGenerator
    {
        $this->class
            ->addMethod(static::camelCase('unset_' . $name))
            ->addComment(
                '@return ' . $this->class->getName()
            )->setReturnType($this->getCanonicalClassName())
            ->setBody(
                '// config is immutable' . PHP_EOL .
                'return $this;'
            );
        return $this;
    }

    /**
     * @return ClassGenerator
     */
    public function generateMagicGet(): ClassGenerator
    {
        $cases = '';

        /** @var Property $property */
        foreach ($this->class->getProperties() as $property) {
            $getter = static::camelCase('get_' . $property->getName());

            try {
                $this->class->getMethod($getter);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            $cases .=
                '    case \'' . substr($property->getName(), 2, -2) . '\':' . PHP_EOL .
                '        return $this->' . $getter . '();' . PHP_EOL . PHP_EOL;
        }

        $this->class
            ->addMethod('__get')
            ->addComment(
                '@inheritDoc'
            )->setBody(
                'switch ($name) {' . PHP_EOL .
                $cases .
                '    default:' . PHP_EOL .
                '        return null;' . PHP_EOL .
                '}'
            )->addParameter('name');
        return $this;
    }

    /**
     * @return ClassGenerator
     */
    public function generateMagicSet(): ClassGenerator
    {
        $cases = '';

        /** @var Property $property */
        foreach ($this->class->getProperties() as $property) {
            $setter = static::camelCase('set_' . $property->getName());

            try {
                $setterMethod = $this->class->getMethod($setter);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            $cases .=
                '    case \'' . substr($property->getName(), 2, -2) . '\':' . PHP_EOL .
                '        return $this->' . $setter . '(' . (0 < count($setterMethod->getParameters()) ? '$value' : '') .
                ');' . PHP_EOL . PHP_EOL;
        }

        $method = $this->class
            ->addMethod('__set')
            ->addComment(
                '@inheritDoc'
            )->setBody(
                'switch ($name) {' . PHP_EOL .
                $cases .
                '    default:' . PHP_EOL .
                '        return null;' . PHP_EOL .
                '}'
            );

        $method->addParameter('name');
        $method->addParameter('value');

        return $this;
    }

    /**
     * @return ClassGenerator
     */
    public function generateMagicIsset(): ClassGenerator
    {
        $cases = '';

        /** @var Property $property */
        foreach ($this->class->getProperties() as $property) {
            $isset = static::camelCase('isset_' . $property->getName());

            try {
                $this->class->getMethod($isset);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            $cases .=
                '    case \'' . substr($property->getName(), 2, -2) . '\':' . PHP_EOL .
                '        return $this->' . $isset . '();' . PHP_EOL . PHP_EOL;
        }

        $this->class
            ->addMethod('__isset')
            ->addComment(
                '@inheritDoc'
            )->setBody(
                'switch ($name) {' . PHP_EOL .
                $cases .
                '    default:' . PHP_EOL .
                '        return false;' . PHP_EOL .
                '}'
            )->addParameter('name');
        return $this;
    }

    /**
     * @return ClassGenerator
     */
    public function generateMagicUnset(): ClassGenerator
    {
        $cases = '';

        /** @var Property $property */
        foreach ($this->class->getProperties() as $property) {
            $unset = static::camelCase('unset_' . $property->getName());

            try {
                $this->class->getMethod($unset);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            $cases .=
                '    case \'' . substr($property->getName(), 2, -2) . '\':' . PHP_EOL .
                '        return $this->' . $unset . '();' . PHP_EOL . PHP_EOL;
        }

        $this->class
            ->addMethod('__unset')
            ->addComment(
                '@inheritDoc'
            )->setBody(
                'switch ($name) {' . PHP_EOL .
                $cases .
                '    default:' . PHP_EOL .
                '        return $this;' . PHP_EOL .
                '}'
            )->addParameter('name');
        return $this;
    }
}