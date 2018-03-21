<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigObject
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigObject implements ConfigEntryTypeHintInterface
{
    /**
     * @var string
     */
    public $class;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->class;
    }
}