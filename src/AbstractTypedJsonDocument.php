<?php

namespace alcamo\openapi;

use alcamo\json\{JsonNode, JsonDocumentTrait};

abstract class AbstractTypedJsonDocument extends JsonNode
{
    use JsonDocumentTrait;

    public function getExpectedNodeClass(string $jsonPtr, $value): string
    {
        if (is_object($value) && isset($value->{'$ref'})) {
            return JsonNode::class;
        }

        $class = static::class;

        for (
            $refToken = strtok($jsonPtr, '/');
            $refToken !== false;
            $refToken = strtok('/')
        ) {
            if (is_numeric($refToken)) {
                continue;
            }

            $class = $class::CLASS_MAP[$refToken] ?? $class::CLASS_MAP['*'];
        }

        return $class;
    }
}
