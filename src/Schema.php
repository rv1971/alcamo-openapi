<?php

namespace alcamo\openapi;

class Schema extends OpenApiNode
{
    public const CLASS_MAP = [
        'discriminator' => Discriminator::class,
        'xml'           => Xml::class,
        'externalDocs'  => ExternalDocumentation::class,
        '*'             => OpenApiNode::class
    ];
}
