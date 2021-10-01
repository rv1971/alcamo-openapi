<?php

namespace alcamo\openapi;

class Schema extends OpenApiNode
{
    public const CLASS_MAP = [
        'properties'           => [ '*' => '#' ],
        'patternProperties'    => [ '*' => '#' ],
        'additionalProperties' => '#',
        'propertyNames'        => '#',
        'items'                => '#',
        'prefixItems'          => [ '*' => '#' ],
        'contains'             => '#',
        'allOf'                => [ '*' => '#' ],
        'anyOf'                => [ '*' => '#' ],
        'oneOf'                => [ '*' => '#' ],
        'not'                  => '#',
        'dependentSchemas'     => [ '*' => '#' ],
        'if'                   => '#',
        'then'                 => '#',
        'else'                 => '#',
        'discriminator'        => Discriminator::class,
        'xml'                  => Xml::class,
        'externalDocs'         => ExternalDocumentation::class,
        'example'              => OpenApiNode::class
    ];
}
