<?php

namespace alcamo\openapi;

use alcamo\json\JsonNode;

/**
 * @brief Generic node of an OpenAPI document
 *
 * All nodes defined in the OpenAPI schema have their own class, so this node
 * is needed for extensions and for embedded examples.
 */
class OpenApiNode extends JsonNode
{
    public const CLASS_MAP = [ '*' => __CLASS__ ];
}
