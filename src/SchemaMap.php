<?php

namespace alcamo\openapi;

use alcamo\json\SchemaMapNode;

/**
 * @brief OpenAPI Schema map node
 *
 * Needed to ensure that, for instance, the sub-nodes in the `properties` JSON
 * object are again OpenAPI schema nodes and not just JSON schema nodes.
 */
class SchemaMap extends SchemaMapNode
{
    public const CLASS_MAP = [ '*' => Schema::class ];
}
