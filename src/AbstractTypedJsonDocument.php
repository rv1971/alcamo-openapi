<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use alcamo\json\{JsonNode, JsonDocumentTrait};

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

                /** @throw DataValidationFailed if no entry is found in
                 *  the class map. */
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
                $class = $childSpec;
            }
        }

        return $class;
    }
}
