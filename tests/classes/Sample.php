<?php

namespace ClassConfig\Test;

use ClassConfig\Annotation\Config;
use ClassConfig\Annotation\ConfigArray;
use ClassConfig\Annotation\ConfigBoolean;
use ClassConfig\Annotation\ConfigFloat;
use ClassConfig\Annotation\ConfigInteger;
use ClassConfig\Annotation\ConfigObject;
use ClassConfig\Annotation\ConfigString;

/**
 * Class Sample
 * @package ClassConfig\Test
 *
 * @Config({
 *     "empty_string":      @ConfigString(),
 *     "empty_integer":     @ConfigInteger(),
 *     "empty_float":       @ConfigFloat(),
 *     "empty_boolean":     @ConfigBoolean(),
 *     "empty_object":      @ConfigObject(class="\DateTime"),
 *     "array_of_mixed":    @ConfigArray(),
 *     "array_of_strings":  @ConfigArray(@ConfigString()),
 *     "array_of_integers": @ConfigArray(@ConfigInteger()),
 *     "array_of_floats":   @ConfigArray(@ConfigFloat()),
 *     "array_of_booleans": @ConfigArray(@ConfigBoolean()),
 *     "array_of_objects":  @ConfigArray(@ConfigObject(class="\DateTime")),
 *     "config":            @Config({
 *          "deep_string":      @ConfigString(default="foo"),
 *          "deep_integer":     @ConfigInteger(default=123),
 *          "deep_config":      @Config({
 *              "deeper_float":     @ConfigFloat(default=123.456),
 *              "deeper_boolean":   @ConfigBoolean(default=true),
 *              "deeper_config":    @Config(
 *                  {"deepest_string": @ConfigString()
 *              })
 *          })
 *     }),
 *     "another_config":    @Config({
 *          "deep_object":              @ConfigObject(class="\DateTime"),
 *          "deep_array_of_strings":    @ConfigArray(@ConfigString(), default={"hello", "foo": "bar"})
 *     })
 * })
 */
class Sample
{}