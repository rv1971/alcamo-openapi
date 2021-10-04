<?php

namespace alcamo\openapi;

class MediaType extends OpenApiNode implements HasExampleInterface
{
    public const CLASS_MAP = [
        'schema'   => Schema::class,
        'example'  => OpenApiNode::class,
        'examples' => Examples::class,
        'encoding' => Encodings::class,
        '*'        => OpenApiNode::class // for extensions
    ];
}
