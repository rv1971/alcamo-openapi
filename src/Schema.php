<?php

namespace alcamo\openapi;

use alcamo\json\SchemaNode;

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
