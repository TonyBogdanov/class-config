<?php

namespace ClassConfig\Test;

use ClassConfig\Annotation\Config;
use ClassConfig\Annotation\ConfigList;
use ClassConfig\Annotation\ConfigBoolean;
use ClassConfig\Annotation\ConfigFloat;
use ClassConfig\Annotation\ConfigInteger;
use ClassConfig\Annotation\ConfigMap;
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
 *     "list_of_mixed":     @ConfigList(),
 *     "list_of_strings":   @ConfigList(@ConfigString()),
 *     "list_of_integers":  @ConfigList(@ConfigInteger()),
 *     "list_of_floats":    @ConfigList(@ConfigFloat()),
 *     "list_of_booleans":  @ConfigList(@ConfigBoolean()),
 *     "list_of_objects":   @ConfigList(@ConfigObject(class="\DateTime")),
 *     "map_of_mixed":      @ConfigMap(),
 *     "map_of_strings":    @ConfigMap(@ConfigString()),
 *     "map_of_integers":   @ConfigMap(@ConfigInteger()),
 *     "map_of_floats":     @ConfigMap(@ConfigFloat()),
 *     "map_of_booleans":   @ConfigMap(@ConfigBoolean()),
 *     "map_of_objects":    @ConfigMap(@ConfigObject(class="\DateTime")),
 *     "config":            @Config({
 *          "deep_string":      @ConfigString(default="foo"),
 *          "deep_integer":     @ConfigInteger(default=123),
 *          "deep_config":      @Config({
 *              "deeper_float":     @ConfigFloat(default=123.456),
 *              "deeper_boolean":   @ConfigBoolean(default=true),
 *              "deeper_config":    @Config({
 *                  "deepest_string":   @ConfigString()
 *              })
 *          })
 *     }),
 *     "another_config":    @Config({
 *          "deep_object":              @ConfigObject(class="\DateTime"),
 *          "deep_array_of_strings":    @ConfigList(@ConfigString(), default={"hello", "foo": "bar"})
 *     })
 * })
 */
class Sample
{}