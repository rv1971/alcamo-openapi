<?php

namespace alcamo\openapi;

use alcamo\json\JsonNode;
use alcamo\uri\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\UriInterface;

/**
 * @brief Generic node of an OpenAPI document
 *
 * All nodes defined in the OpenAPI schema have their own class, so this node
 * is needed as a base class, for extensions and for embedded examples.
 */
class OpenApiNode extends JsonNode
{
    public const CLASS_MAP = [ '*' => __CLASS__ ];

    protected function rebase(UriInterface $oldBase): void
    {
        if (isset($this->externalValue)) {
            $this->externalValue = (string)UriResolver::resolve(
                $oldBase,
                new Uri($this->externalValue)
            );
        }
    }
}
