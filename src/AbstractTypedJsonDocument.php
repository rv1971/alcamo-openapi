<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use alcamo\json\{JsonNode, JsonDocumentTrait};

/**
 * @brief JSON node class that creates child nodes of specific classes
 *
 * Each non-abstract child class must have a public class constant
 * `CLASS_MAP`, or must have an ancestor having such a constant. The value of
 * this constant must an associative array mapping property names to class
 * names or nested maps. When a property of a node is a JSON object, it is
 * created as an instance of the indicated class for that property.
 *
 * The property `*` applies to all properties which are not explicitely listed
 * in the map. An alcamo::exception::DataValidationFailed exception is thrown
 * if a property having a JSON obejct value is not listed in the map and no
 * `*` entry exists.
 *
 * A nested map is an associative array whose values are again class names or
 * nested maps. When the value of a property is a JSON array, the nested map
 * is used to determine the classes to use for the single array elements.
 *
 * The value `#` can be used instead of a class name to indicate the
 * current class, which may be a child class of the class that declares
 * the class map.
 */
abstract class AbstractTypedJsonDocument extends JsonNode
{
    use JsonDocumentTrait;

    public function getExpectedNodeClass(string $jsonPtr, $value): string
    {
        if (
            is_object($value)
            && isset($value->{'$ref'})
            && is_string($value->{'$ref'})
        ) {
            return JsonNode::class;
        }

        $class = static::class;

        for (
            $refToken = strtok($jsonPtr, '/');
            $refToken !== false;
            $refToken = strtok('/')
        ) {
            $refToken = str_replace([ '~0', '~1' ], [ '~', '/' ], $refToken);

            if (!isset($map)) {
                $map = $class::CLASS_MAP;
            }

            try {
                $childSpec = $map[$refToken] ?? $map['*'];
            } catch (\Throwable $e) {
                $uri = $this->getBaseUri() . "#$jsonPtr";

                /** @throw alcamo::exception::DataValidationFailed if no entry
                 *  is found in the class map. */
                throw new DataValidationFailed(
                    $refToken,
                    $uri,
                    null,
                    "\"$refToken\" at \"$uri\" not found in map"
                );
            }

            if (is_array($childSpec)) {
                $map = $childSpec;
            } else {
                unset($map);

                if ($childSpec != '#') {
                    $class = $childSpec;
                }
            }
        }

        return $class;
    }
}
