<?php

namespace alcamo\openapi;

use alcamo\json\{JsonNode, SchemaMapNode, SchemaNode};

/**
 * @brief OpenAPI Schema node
 *
 * Contains some OpenAPI 3.0 extensions.
 */
class Schema extends SchemaNode implements HasExampleInterface
{
    public const CLASS_MAP = [
        '$defs'                => SchemaMapNode::class,
        'additionalProperties' => __CLASS__,
        'allOf'                => [ '*' => __CLASS__ ],
        'anyOf'                => [ '*' => __CLASS__ ],
        'contains'             => __CLASS__,
        'default'              => JsonNode::class,
        'dependentSchemas'     => SchemaMapNode::class,
        'else'                 => __CLASS__,
        'examples'             => [ '*' => __CLASS__ ],
        'if'                   => __CLASS__,
        'items'                => __CLASS__,
        'not'                  => __CLASS__,
        'oneOf'                => [ '*' => __CLASS__ ],
        'patternProperties'    => SchemaMapNode::class,
        'prefixItems'          => [ '*' => __CLASS__ ],
        'properties'           => SchemaMapNode::class,
        'propertyNames'        => __CLASS__,
        'then'                 => __CLASS__,

        // OpenAPI-specific
        'properties'           => SchemaMap::class,
        'discriminator'        => Discriminator::class,
        'xml'                  => Xml::class,
        'externalDocs'         => ExternalDocumentation::class,
        'example'              => OpenApiNode::class,
        '*'                    => OpenApiNode::class // for extensions
    ];
}
