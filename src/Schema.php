<?php

namespace alcamo\openapi;

use alcamo\json\SchemaNode;

/**
 * @brief OpenAPI Schema node
 *
 * Contains some OpenAPI 3.0 extensions.
 */
class Schema extends SchemaNode
{
    public const CLASS_MAP =
        [
        'properties'    => SchemaMap::class,
        'discriminator' => Discriminator::class,
        'xml'           => Xml::class,
        'externalDocs'  => ExternalDocumentation::class,
        'example'       => OpenApiNode::class
        ]
        + parent::CLASS_MAP;
}
