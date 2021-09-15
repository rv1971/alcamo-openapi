<?php

namespace alcamo\openapi;

use alcamo\json\JsonNode;
use Psr\Http\Message\UriInterface;

abstract class AbstractTypedJsonNode extends JsonNode
{
    public function createNodeObject(
        $data,
        ?JsonNode $parent = null,
        ?string $key = null,
        ?UriInterface $baseUri = null
    ): JsonNode {
        $key = explode('/', $key, 2)[0];

        $className = $parent::CLASS_MAP[$key] ?? $parent::CLASS_MAP['*'];

        return new $className($data, $parent, $key, $baseUri);
    }
}
