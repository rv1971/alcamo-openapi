<?php

namespace alcamo\openapi;

class MediaType extends OpenApiNode
{
    public const CLASS_MAP = [
        'schema'   => Schema::class,
        'examples' => Examples::class,
        'encoding' => Encodings::class
    ];
}
