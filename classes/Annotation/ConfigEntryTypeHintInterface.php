<?php

namespace ClassConfig\Annotation;

/**
 * Interface ConfigEntryTypeHintInterface
 * @package ClassConfig\Annotation
 */
interface ConfigEntryTypeHintInterface extends ConfigEntryInterface
{
    /**
     * @return string
     */
    public function getType(): string;
}