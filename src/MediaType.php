<?php

namespace alcamo\openapi;

class MediaType extends OpenApiNode
{
    public const CLASS_MAP = [
        'schema'   => Schema::class,
        'example'  => OpenApiNode::class,
        'examples' => Examples::class,
        'encoding' => Encodings::class,
        '*'        => OpenApiNode::class // for extensions
    ];
}
