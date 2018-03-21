<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigInteger
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigInteger implements ConfigEntryTypeHintInterface
{
    /**
     * @var int
     */
    public $default;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'int';
    }
}